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

use EasyWeChat\Work\Application;
use GuzzleHttp\RequestOptions;
use Han\Utils\Service;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\Inject;

class WeChatService extends Service
{
    #[Inject]
    protected Application $application;

    #[Inject]
    protected ConfigInterface $config;

    public function authorize(string $url): string
    {
        return sprintf(
            'https://open.work.weixin.qq.com/wwopen/sso/qrConnect?appid=%s&agentid=%s&redirect_uri=%s&state=STATE',
            $this->config->get('wechat.default.corp_id'),
            $this->config->get('wechat.default.agent_id'),
            $url
        );
    }

    public function getUserInfo(string $code): array
    {
        $user = $this->application->getOAuth()->userFromCode($code);

        $userId = $user->getId();

        $res = $this->application->getClient()->get('/cgi-bin/user/get', [
            RequestOptions::QUERY => [
                'userid' => $userId,
            ],
        ])->toArray();

        return [
            'name' => $res['name'],
            'open_id' => $res['userid'],
            'mobile' => $res['mobile'],
            'email' => $res['biz_mail'],
            'avatar_url' => $res['thumb_avatar'],
        ];
    }
}
