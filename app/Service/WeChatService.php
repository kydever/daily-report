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
use App\Constants\Event;
use App\Exception\BusinessException;
use App\Model\ReportItem;
use EasyWeChat\Kernel\Form\File;
use EasyWeChat\Kernel\Form\Form;
use EasyWeChat\Work\Application;
use GuzzleHttp\RequestOptions;
use Han\Utils\Service;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\Model\Collection;
use Hyperf\Di\Annotation\Inject;

class WeChatService extends Service
{
    #[Inject]
    protected Application $application;

    #[Inject]
    protected ConfigInterface $config;

    public function isEnable(): bool
    {
        return $this->config->get('wechat.default.agent_id') && $this->config->get('wechat.default.corp_id');
    }

    public function authorize(string $url, string $state): string
    {
        return sprintf(
            'https://open.work.weixin.qq.com/wwopen/sso/qrConnect?appid=%s&agentid=%s&redirect_uri=%s&state=%s',
            $this->application->getAccount()->getCorpId(),
            $this->getAgentId(),
            $url,
            $state
        );
    }

    public function getUserInfoByOpenId(string $openId): array
    {
        $res = $this->application->getClient()->get('/cgi-bin/user/get', [
            RequestOptions::QUERY => [
                'userid' => $openId,
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

    public function getUserInfo(string $code): array
    {
        $user = $this->application->getOAuth()->userFromCode($code);

        return $this->getUserInfoByOpenId($user->getId());
    }

    public function setMenu(): void
    {
        $res = $this->application->getClient()->post('/cgi-bin/menu/create', [
            RequestOptions::QUERY => [
                'agentid' => $this->getAgentId(),
            ],
            RequestOptions::JSON => [
                'button' => [
                    [
                        'type' => 'view',
                        'name' => '日报系统',
                        'url' => $this->config->get('frontend.base_uri'),
                    ],
                    [
                        'name' => '快捷入口',
                        'sub_button' => [
                            [
                                'type' => 'click',
                                'name' => '开始工作',
                                'key' => Event::BEGIN_TODAY_WORK,
                            ],
                            [
                                'type' => 'click',
                                'name' => '日报小节',
                                'key' => Event::SHOW_TODAY_REPORT,
                            ],
                            [
                                'type' => 'click',
                                'name' => '日报详情',
                                'key' => Event::SHOW_ALL_TODAY_REPORT,
                            ],
                        ],
                    ],
                ],
            ],
        ])->toArray();

        if ($res['errcode'] !== 0) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, $res['errmsg']);
        }
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

    public function uploadMedia(string $path): string
    {
        $options = Form::create([
            'media' => File::fromPath($path),
        ])->toArray();

        $res = $this->application->getClient()->post('/cgi-bin/media/upload', array_merge([
            RequestOptions::QUERY => [
                'type' => 'file',
            ],
        ], $options))->toArray();

        if ($res['errcode'] !== 0) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, $res['errmsg']);
        }

        return $res['media_id'];
    }

    /**
     * @param Collection<int, ReportItem> $items
     */
    public function sendCard(string $openId, Collection $items): void
    {
        $list = [];
        $count = $items->count();
        $items = $items->sortByDesc('id');
        $limit = 6;
        foreach ($items as $item) {
            $list[] = ['keyname' => $item->project, 'value' => $item->summary];
            if (count($list) >= $limit) {
                break;
            }
        }
        $res = $this->application->getClient()->post('/cgi-bin/message/send', [
            RequestOptions::JSON => [
                'msgtype' => 'template_card',
                'agentid' => $this->getAgentId(),
                'template_card' => [
                    'card_type' => 'text_notice',
                    'source' => [
                        'icon_url' => 'https://avatars.githubusercontent.com/u/81892392?s=200&v=4',
                        'desc' => '日报系统',
                        'desc_color' => 1,
                    ],
                    'main_title' => [
                        'title' => '日报列表',
                        'desc' => '感谢您为 KY 的付出',
                    ],
                    'emphasis_content' => [
                        'desc' => '今日完成任务',
                        'title' => $count,
                    ],
                    'sub_title_text' => '以下是已完成的任务列表',
                    'horizontal_content_list' => $list,
                    'card_action' => [
                        'type' => 1,
                        'url' => $this->config->get('frontend.base_uri'),
                    ],
                ],
                'touser' => $openId,
            ],
        ])->toArray();

        if ($res['errcode'] !== 0) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, $res['errmsg']);
        }
    }

    public function sendText(string $openId, string $content): void
    {
        $res = $this->application->getClient()->post('/cgi-bin/message/send', [
            RequestOptions::JSON => [
                'msgtype' => 'text',
                'agentid' => $this->getAgentId(),
                'text' => [
                    'content' => $content,
                ],
                'touser' => $openId,
            ],
        ])->toArray();

        if ($res['errcode'] !== 0) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, $res['errmsg']);
        }
    }

    public function sendMedia(string $openId, string $mediaId): void
    {
        $res = $this->application->getClient()->post('/cgi-bin/message/send', [
            RequestOptions::JSON => [
                'msgtype' => 'file',
                'agentid' => $this->getAgentId(),
                'file' => [
                    'media_id' => $mediaId,
                ],
                'touser' => $openId,
            ],
        ])->toArray();

        if ($res['errcode'] !== 0) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, $res['errmsg']);
        }
    }

    public function setWorkBenchData(string $userId, int $todayCount, int $weekCount, int $monthCount)
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
                            'data' => $todayCount,
                        ],
                        [
                            'key' => '本周',
                            'data' => $weekCount,
                        ],
                        [
                            'key' => '本月',
                            'data' => $monthCount,
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
