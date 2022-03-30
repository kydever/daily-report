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
namespace App\Controller;

use EasyWeChat\Work\Application;
use Hyperf\Di\Annotation\Inject;

class WeChatController extends Controller
{
    #[Inject]
    protected Application $application;

    public function checkServe()
    {
        $server = $this->application->getServer();

        return (string) $server->serve()->getBody();
    }

    public function serve()
    {
        $server = $this->application->getServer();
        $server->with(function ($message, \Closure $next) {
            var_dump($message);
            return $next($message);
        });
        return (string) $server->serve()->getBody();
    }
}
