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

use App\Service\Dao\UserDao;
use App\Service\FeishuService;
use App\Service\UserAuth;
use Hyperf\Di\Annotation\Inject;

class OauthController extends Controller
{
    #[Inject]
    protected FeishuService $feishu;

    #[Inject]
    protected UserDao $dao;

    public function authorize()
    {
        $url = $this->request->input('redirect_uri');

        return $this->response->redirect(
            $this->feishu->getApplication()->oauth->authorize($url)
        );
    }

    public function login()
    {
        $code = $this->request->input('code');

        $result = $this->feishu->getApplication()->oauth->getUserInfo($code);

        $openId = $result['open_id'];

        $user = $this->dao->firstByOpenId($openId);

        $token = UserAuth::instance()->init($user)->getToken();

        return $this->response->success([
            'token' => $token,
        ]);
    }
}
