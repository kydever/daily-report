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

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
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

    public function setWorkBenchTemplate(): void
    {
        $res = $this->application->getClient()->post('/cgi-bin/agent/set_workbench_template', [
            RequestOptions::JSON => [
                'agentid' => $this->getAgentId(),
                'type' => 'keydata',
                'items' => [
                    [
                        'key' => '本日',
                        'data' => '0',
                    ],
                    [
                        'key' => '本周',
                        'data' => '0',
                    ],
                    [
                        'key' => '本月',
                        'data' => '0',
                    ],
                ],
            ],
        ])->toArray();

        if ($res['errcode'] !== 0) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, $res['errmsg']);
        }
    }

    public function setWorkBenchData(string $userId): bool
    {
        $res = $this->application->getClient()->post('/cgi-bin/agent/set_workbench_data', [
            RequestOptions::JSON => [
                'agentid' => $this->getAgentId(),
                'userid' => $userId,
                'type' => 'keydata',
                'keydata' => [
                    'items' => [
                        [
                            'key' => '本日',
                            'data' => '0',
                        ],
                        [
                            'key' => '本周',
                            'data' => '0',
                        ],
                        [
                            'key' => '本月',
                            'data' => '0',
                        ],
                    ],
                ],
            ],
        ])->toArray();

        if ($res['errcode'] !== 0) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, $res['errmsg']);
        }
    }

    protected function getAgentId(): int
    {
        return $this->config->get('wechat.default.agent_id');
    }
}
