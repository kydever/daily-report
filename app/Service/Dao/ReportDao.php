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

use App\Model\Report;
use Carbon\Carbon;
use Han\Utils\Service;

class ReportDao extends Service
{
    public function firstByUserId(int $userId, ?string $date = null): ?Report
    {
        return Report::query()->where('user_id', $userId)
            ->where('dt', $date ?? date('Y-m-d'))
            ->first();
    }

    public function firstOrCreate(int $userId): Report
    {
        $model = $this->firstByUserId($userId);
        if (empty($model)) {
            $model = new Report();
            $model->user_id = $userId;
            $model->dt = Carbon::now()->toDateString();
            $model->score = 0;
            $model->save();
        }

        return $model;
    }
}
