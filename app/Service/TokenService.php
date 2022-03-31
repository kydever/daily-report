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

use Han\Utils\Service;

class TokenService extends Service
{
    public function generate(string $data): string
    {
        $token = password_hash($data, PASSWORD_DEFAULT, PASSWORD_BCRYPT);
        if (! $token) {
            $this->generate($data);
        }

        return $token;
    }
}
