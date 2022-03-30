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
    'scan' => [
        'paths' => [
            BASE_PATH . '/app',
            BASE_PATH . '/rpc',
        ],
        'ignore_annotations' => [
            'mixin',
        ],
        'class_map' => [
            Hyperf\Utils\Coroutine::class => BASE_PATH . '/app/Kernel/ClassMap/Coroutine.php',
            Hyperf\Di\Resolver\ResolverDispatcher::class => BASE_PATH . '/app/Kernel/ClassMap/ResolverDispatcher.php',
            EasyWeChat\Kernel\Traits\InteractWithHttpClient::class => BASE_PATH . '/app/Kernel/ClassMap/InteractWithHttpClient.php',
        ],
    ],
];
