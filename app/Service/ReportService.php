<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Service;

use App\Constants\ErrorCode;
use App\Constants\Event;
use App\Constants\OAuth;
use App\Exception\BusinessException;
use App\Model\ReportItem;
use App\Service\Dao\ReportDao;
use App\Service\Dao\ReportItemDao;
use App\Service\Dao\UserDao;
use App\Service\Formatter\ReportFormatter;
use Carbon\Carbon;
use Han\Utils\Service;
use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;
use function Han\Utils\date_load;

class ReportService extends Service
{
    #[Inject]
    protected ReportDao $dao;

    #[Inject]
    protected ReportItemDao $item;

    #[Inject]
    protected ReportFormatter $formatter;

    public function find(int $userId, int $offset = 0, int $limit = 5): array
    {
        $models = $this->dao->find($userId, $offset, $limit);

        $models->load('items');

        return $this->formatter->formatList($models);
    }

    public function deleteItem(int $id, int $userId): bool
    {
        $model = $this->item->first($id, false);
        if (! $model) {
            return false;
        }

        $report = $this->dao->firstOrCreate($userId);

        if ($model->user_id !== $userId) {
            throw new BusinessException(ErrorCode::PERMISSION_INVALID);
        }

        if ($model->report_id !== $report->id) {
            throw new BusinessException(ErrorCode::REPORT_ITEM_CANNOT_UPDATE);
        }

        return $model->delete();
    }

    public function addItem(int $id, int $userId, string $project, string $module, string $summary, string|Carbon $beginTime, string|Carbon $endTime): ReportItem
    {
        $report = $this->dao->firstOrCreate($userId);
        if ($id === 0) {
            $model = $this->item->new($userId, $report->id);
        } else {
            $model = $this->item->first($id, true);
            if ($model->report_id !== $report->id) {
                throw new BusinessException(ErrorCode::REPORT_ITEM_CANNOT_UPDATE);
            }
        }

        if ($model->user_id !== $userId) {
            throw new BusinessException(ErrorCode::PERMISSION_INVALID);
        }

        $beginTime = date_load($beginTime);
        $endTime = date_load($endTime);

        $model->project = $project;
        $model->module = $module;
        $model->summary = $summary;
        $model->begin_time = $beginTime->format('H:i');
        $model->end_time = $endTime?->format('H:i');
        $model->used_time = ($endTime?->getTimestamp() ?? $beginTime->getTimestamp()) - $beginTime->getTimestamp();
        $model->save();

        return $model;
    }

    #[AsyncQueueMessage(delay: 180)]
    public function sendReportToWorkBench(int $userId): void
    {
        if (OAuth::isWorkWechat() && di()->get(WeChatService::class)->isEnable()) {
            if ($user = di()->get(UserDao::class)->first($userId)) {
                [$todayCount, $weekCount, $monthCount] = $this->getReportData($userId);

                di()->get(WeChatService::class)->setWorkBenchData($user->open_id, $todayCount, $weekCount, $monthCount);
            }
        }
    }

    public function getReportData(int $userId): array
    {
        $now = Carbon::now();
        $today = $now->clone()->startOfDay();
        $week = $now->clone()->startOfWeek();
        $month = $now->clone()->startOfMonth();

        return [
            $this->item->countByUserId($userId, $today->toDateTimeString()),
            $this->item->countByUserId($userId, $week->toDateTimeString()),
            $this->item->countByUserId($userId, $month->toDateTimeString()),
        ];
    }

    #[AsyncQueueMessage]
    public function handleWeChatEvent(string $openId, string $event): void
    {
        $user = di()->get(UserDao::class)->firstByOpenId($openId);
        if (empty($user)) {
            $result = di()->get(WeChatService::class)->getUserInfoByOpenId($openId);
            $user = di()->get(UserDao::class)->firstOrCreate($result);
        }

        switch ($event) {
            case Event::SHOW_TODAY_REPORT:
                if ($model = di()->get(ReportDao::class)->firstByUserId($user->id)) {
                    $items = di()->get(ReportItemDao::class)->findByReportId($model->id);
                    di()->get(WeChatService::class)->sendCard($user->open_id, $items);
                }
                break;
            case Event::BEGIN_TODAY_WORK:
                WorkToday::load($user)
                    ->begin()
                    ->save()
                    ->afterHandle();
                break;
            case Event::SHOW_ALL_TODAY_REPORT:
                // TODO: 生成我的日报Excel
                // TODO: 上传临时素材
                // TODO: 发送给用户
                break;
        }
    }

    #[AsyncQueueMessage]
    public function handleWeChatMessage(string $openId, string $content): void
    {
        $user = di()->get(UserDao::class)->firstByOpenId($openId);
        if (empty($user)) {
            $result = di()->get(WeChatService::class)->getUserInfoByOpenId($openId);
            $user = di()->get(UserDao::class)->firstOrCreate($result);
        }

        $work = WorkToday::load($user);
        if ($work->isStart()) {
            if ($content === '退出') {
                $work->quit()->save()->afterHandle();
                return;
            }

            $work->handle($content)->next()->save()->afterHandle();

            return;
        }

        if ($content === '我的日报') {
            if ($model = di()->get(ReportDao::class)->firstByUserId($user->id)) {
                $items = di()->get(ReportItemDao::class)->findByReportId($model->id);
                di()->get(WeChatService::class)->sendCard($user->open_id, $items);
            }
            return;
        }

        $data = explode(PHP_EOL, $content);
        $data = array_filter($data);
        if (count($data) === 6 && array_shift($data) === '日报') {
            $model = di()->get(ReportService::class)->addItem(0, $user->id, ...$data);
            if ($model->id > 0) {
                // 创建成功
                di()->get(WeChatService::class)->sendText($user->open_id, '收到了您的日报，请继续为KY创造价值吧。');
            }
        }
    }

    public function generateToken(int $reportId): string
    {
        $token = md5(uniqid() . $reportId);

        di()->get(Redis::class)->set('report:' . $token, (string) $reportId, 3600);

        return $token;
    }

    public function getReportIdFromToken(string $token): int
    {
        $id = (int) di()->get(Redis::class)->get('report:' . $token);
        if (! $id) {
            throw new BusinessException(ErrorCode::REPORT_TOKEN_EXPIRED);
        }

        return $id;
    }

    public function items(int $reportId)
    {
        $model = $this->dao->first($reportId, true);

        $model->load('items');

        return $this->formatter->base($model);
    }

    public function exportItem(int $reportId): string
    {
        $models = $this->item->findByReportId($reportId);
        $fileName = BASE_PATH . '/runtime/' . $reportId . '_' . time() . '.csv';
        if (file_exists($fileName)) {
            unlink($fileName);
        }
        $stream = fopen($fileName, 'w+');
        fputcsv($stream, ['id', '项目', '模块', '工作详情', '进度', '时间']);

        /** @var ReportItem $v */
        foreach ($models as $v) {
            $data = [
                'id' => $v->id,
                'project' => $v->project,
                'module' => $v->module,
                'summary' => $v->summary,
                'schedule' => ReportItem::SCHEDULE_DEFAULT,
                'date' => $v->begin_time . ' - ' . $v->end_time,
            ];
            fputcsv($stream, $data);
        }
        fclose($stream);

        return $fileName;
    }
}
