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
use Han\Utils\Service;

class ReportDao extends Service
{
    public function firstByUserId(int $userId, ?string $date = null): ?Report
    {
        return Report::query()->where('user_id', $userId)
            ->where('dt', $date ?? date('Y-m-d'))
            ->first();
    }
}
