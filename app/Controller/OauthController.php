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

use App\Constants\OAuth;
use App\Service\Dao\UserDao;
use App\Service\FeishuService;
use App\Service\Formatter\UserFormatter;
use App\Service\TokenService;
use App\Service\UserAuth;
use App\Service\WeChatService;
use Hyperf\Cache\Driver\RedisDriver;
use Hyperf\Config\Annotation\Value;
use Hyperf\Di\Annotation\Inject;

class OauthController extends Controller
{
    #[Inject]
    protected FeishuService $feishu;

    #[Inject]
    protected UserDao $dao;

    #[Inject]
    protected UserFormatter $formatter;

    #[Inject]
    protected WeChatService $work;

    #[Value(key: 'oauth')]
    protected int $oauth;

    public function authorize()
    {
        $url = $this->request->input('redirect_uri');
        $redirectUrl = match ($this->oauth) {
            OAuth::FEISHU => $this->feishu->getApplication()->oauth->authorize($url),
            OAuth::WORK_WECHAT => $this->work->authorize($url),
        };

        return $this->response->redirect($redirectUrl);
    }

    public function login()
    {
        $code = $this->request->input('code');

        $result = match ($this->oauth) {
            OAuth::FEISHU => $this->feishu->getApplication()->oauth->getUserInfo($code),
            OAuth::WORK_WECHAT => $this->work->getUserInfo($code),
        };

        $user = $this->dao->firstOrCreate($result);

        $token = UserAuth::instance()->init($user)->getToken();

        return $this->response->success([
            'token' => $token,
            'user' => $this->formatter->base($user),
        ]);
    }

    public function generateToken()
    {
        $user = UserAuth::instance()->build()->getUser();
        $token = di()->get(TokenService::class)->generate($user->email);
        di()->get(RedisDriver::class)->set($token, $user->id);
    }
}
