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
namespace App\Service\Formatter;

use App\Model\ReportItem;
use Han\Utils\Service;

class ReportItemFormatter extends Service
{
    public function base(ReportItem $model): array
    {
        return [
            'id' => $model->id,
            'user_id' => $model->user_id,
            'project' => $model->project,
            'module' => $model->module,
            'summary' => $model->summary,
            'begin_time' => $model->begin_time,
            'end_time' => $model->end_time,
            'used_time' => $model->used_time,
            'extra' => $model->extra,
        ];
    }

    public function formatList($models): array
    {
        $results = [];

        foreach ($models as $model) {
            $results[] = $model;
        }

        return $results;
    }
}
