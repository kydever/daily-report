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

use Hyperf\Contract\ConfigInterface;

class OAuth
{
    public const WORK_WECHAT = 1;

    public const FEISHU = 2;

    public static function isWorkWechat(): bool
    {
        return di()->get(ConfigInterface::class)->get('oauth') === self::WORK_WECHAT;
    }
}
