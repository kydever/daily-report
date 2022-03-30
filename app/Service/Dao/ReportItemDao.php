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
namespace App\Service\Dao;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Model\ReportItem;
use Han\Utils\Service;

class ReportItemDao extends Service
{
    public function first(int $id, bool $throw = false): ?ReportItem
    {
        $model = ReportItem::query()->find($id);
        if (empty($model) && $throw) {
            throw new BusinessException(ErrorCode::REPORT_ITEM_NOT_EXIST);
        }
        return $model;
    }

    public function findByReportId(int $reportId)
    {
        return ReportItem::query()->where('report_id', $reportId)->get();
    }

    public function new(int $userId, int $reportId): ReportItem
    {
        $model = new ReportItem();
        $model->user_id = $userId;
        $model->report_id = $reportId;
        return $model;
    }

    public function countByUserId(int $userId, string $beginAt): int
    {
        return ReportItem::query()
            ->where('user_id', $userId)
            ->where('created_at', '>=', $beginAt)
            ->count();
    }
}
