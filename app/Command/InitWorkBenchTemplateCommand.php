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
namespace App\Command;

use App\Service\WeChatService;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;

#[Command]
class InitWorkBenchTemplateCommand extends HyperfCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('init:work');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('初始化企业微信');
    }

    public function handle()
    {
        // 初始化企业微信工作台
        di()->get(WeChatService::class)->setWorkBenchTemplate();
        // 初始化企业微信自定义菜单
        di()->get(WeChatService::class)->setMenu();
    }
}
