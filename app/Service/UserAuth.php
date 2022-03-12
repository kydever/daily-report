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

use App\Constants\Environment;
use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Model\User;
use Hyperf\Redis\Redis;
use Hyperf\Utils\Traits\StaticInstance;

class UserAuth
{
    use StaticInstance;

    public const TOP_SESSION = 'topsession';

    public const PREFIX = 'auth:';

    protected int $userId = 0;

    protected string $token = '';

    public function reload(string $token): static
    {
        $data = di()->get(Redis::class)->get($this->getInnerToken($token));

        if ($data) {
            $data = unserialize($data);
            $this->userId = $data['user_id'] ?? 0;
        }

        return $this;
    }

    public function init(User $user)
    {
        $this->userId = $user->id;
        $this->token = md5($user->id . uniqid());

        di()->get(Redis::class)->set($this->getInnerToken($this->token), ['user_id' => $user->id], 86400);

        return $this;
    }

    public function build(): static
    {
        if ($this->userId === 0) {
            throw new BusinessException(ErrorCode::TOKEN_INVALID);
        }

        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        if (Environment::isProd()) {
            throw new BusinessException(ErrorCode::SERVER_ERROR, '线上环境禁止修改 userId');
        }
        $this->userId = $userId;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    private function getInnerToken(string $token): string
    {
        return self::PREFIX . $token;
    }
}
