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

use Fan\Feishu\Application;
use Fan\Feishu\Factory;
use Han\Utils\Service;

class FeishuService extends Service
{
    public function getApplication(): Application
    {
        return di()->get(Factory::class)->get('default');
    }
}
