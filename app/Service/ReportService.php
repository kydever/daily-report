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
use App\Exception\BusinessException;
use App\Model\ReportItem;
use App\Service\Dao\ReportDao;
use App\Service\Dao\ReportItemDao;
use App\Service\Formatter\ReportFormatter;
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;
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

        if ($model->user_id !== $userId) {
            throw new BusinessException(ErrorCode::PERMISSION_INVALID);
        }

        return $model->delete();
    }

    public function addItem(int $id, int $userId, string $project, string $module, string $summary, string $beginTime, string $endTime): ReportItem
    {
        if ($id === 0) {
            $report = $this->dao->firstOrCreate($userId);
            $model = $this->item->new($userId, $report->id);
        } else {
            $model = $this->item->first($id, true);
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
}
