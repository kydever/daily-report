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
return [
    'default' => [
        'corp_id' => env('WORK_WECHAT_CORP_ID'),
        'agent_id' => (int) env('WORK_WECHAT_AGENT_ID'),
        'secret' => env('WORK_WECHAT_SECRET'),
        'token' => env('WORK_WECHAT_TOKEN'),
        'aes_key' => env('WORK_WECHAT_AES_KEY'),
        'oauth' => [
            'redirect_url' => '',
        ],
    ],
];
