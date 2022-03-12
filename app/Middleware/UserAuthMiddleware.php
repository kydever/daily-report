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
namespace App\Middleware;

use App\Service\UserAuth;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UserAuthMiddleware implements MiddlewareInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $request->getHeaderLine(UserAuth::TOP_SESSION);

        if ($token) {
            UserAuth::instance()->reload($token);
        }

        if (env('DEV_AUTH_DEBUG', false) && UserAuth::instance()->getUserId() === 0) {
            // 设置默认的开发阶段的登录状态
            UserAuth::instance()->setUserId(1);
        }

        return $handler->handle($request);
    }
}
