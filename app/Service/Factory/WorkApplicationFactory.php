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
namespace App\Service\Factory;

use App\Service\ReportService;
use EasyWeChat\Work\Application;
use EasyWeChat\Work\Message;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\Codec\Json;
use Psr\Container\ContainerInterface;

class WorkApplicationFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class)->get('wechat.default', []);

        return tap(new Application($config), static function (Application $application) {
            $application->setRequest(di()->get(RequestInterface::class));
            $application->getServer()->with(function (Message $message, \Closure $next) {
                di()->get(StdoutLoggerInterface::class)->info(Json::encode($message->toArray()));
                switch ($message->MsgType) {
                    case 'text':
                        if ($content = $message->Content) {
                            di()->get(ReportService::class)->handleWeChatMessage($message->FromUserName, $content);
                        }
                        break;
                    case 'event':
                        if ($key = $message->EventKey) {
                            di()->get(ReportService::class)->handleWeChatEvent($message->FromUserName, $key);
                        }
                        break;
                }
                return $next($message);
            });
        });
    }
}
