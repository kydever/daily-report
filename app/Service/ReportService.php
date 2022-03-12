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
use Han\Utils\Service;
use Hyperf\Di\Annotation\Inject;
use function Han\Utils\date_load;

class ReportService extends Service
{
    #[Inject]
    protected ReportDao $dao;

    #[Inject]
    protected ReportItemDao $item;

    public function addItem(int $id, int $userId, string $project, string $module, string $summary, string $beginTime, string $endTime): ReportItem
    {
        if ($id === 0) {
            $report = $this->dao->firstByUserId($userId);
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
        $model->begin_time = $beginTime->format('i:s');
        $model->end_time = $endTime?->format('i:s');
        $model->used_time = ($endTime?->getTimestamp() ?? $beginTime->getTimestamp()) - $beginTime->getTimestamp();
        $model->save();

        return $model;
    }
}
