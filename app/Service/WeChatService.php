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
use Hyperf\Utils\Arr;

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
            $this->application->getAccount()->getCorpId(),
            $this->getAgentId(),
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

    public function setWorkBenchTemplate(): bool
    {
        $workBench = $this->config->get('wechat.default.work_bench');
        $json = Arr::only($workBench, ['type', 'webview']);
        $res = $this->application->getClient()->post('/cgi-bin/agent/set_workbench_template', [
            RequestOptions::JSON => array_merge(['agentid' => $this->getAgentId()], $json),
        ])->toArray();

        return $res['errcode'] === 0;
    }

    public function setWorkBenchData(string $userId): bool
    {
        $workBench = $this->config->get('wechat.default.work_bench');
        $json = array_merge(Arr::only($workBench, ['type', 'webview']), [
            'agentid' => $this->getAgentId(),
            'userid' => $userId,
        ]);

        $res = $this->application->getClient()->post('/cgi-bin/agent/set_workbench_data', [
            RequestOptions::JSON => $json,
        ])->toArray();

        var_dump($res);

        return $res['errcode'] === 0;
    }

    protected function getAgentId(): int
    {
        return $this->config->get('wechat.default.agent_id');
    }
}
