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
namespace App\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $report_id
 * @property string $project
 * @property string $module
 * @property string $summary
 * @property string $begin_time
 * @property string $end_time
 * @property string $extra
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ReportItem extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'report_items';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'user_id', 'report_id', 'project', 'module', 'summary', 'begin_time', 'end_time', 'extra', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'report_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}
