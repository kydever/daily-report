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
namespace App\Constants;

class Status
{
    // 草稿
    public const INIT = 0;

    // 上线
    public const ONLINE = 1;

    // 下线
    public const OFFLINE = 2;

    // 删除
    public const DELETED = 3;
}
