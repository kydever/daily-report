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
namespace App\Service\Dao;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Model\User;
use Han\Utils\Service;

class UserDao extends Service
{
    public function firstByMobile(string $mobile): ?User
    {
        return User::where('mobile', $mobile)->first();
    }

    public function firstByOpenId(string $openId): ?User
    {
        return User::query()->where('open_id', $openId)->first();
    }

    /**
     * @param $input = [
     *     'name' => '',
     *     'mobile' => '',
     *     'email' => '',
     *     'avatar_url' => '',
     *     'open_id' => '',
     * ]
     */
    public function firstOrCreate(array $input = []): User
    {
        $openId = $input['open_id'];
        $model = $this->firstByOpenId($openId);
        if (empty($model)) {
            $model = new User();
            $model->open_id = $openId;
        }

        $model->fill($input);
        $model->save();
        return $model;
    }

    public function first(int $id, bool $throw = false): ?User
    {
        $model = User::findFromCache($id);
        if (empty($model) && $throw) {
            throw new BusinessException(ErrorCode::USER_NOT_EXIST);
        }

        return $model;
    }
}
