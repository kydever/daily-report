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

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Han\Utils\Service;
use Hyperf\Utils\Codec\Json;

class WeChatRobot extends Service
{
    public function sendText(string $key, string $text)
    {
        return $this->send($key, [
            'msgtype' => 'text',
            'text' => [
                'content' => $text,
            ],
        ]);
    }

    public function send(string $key, array $body): bool
    {
        $client = new Client([
            'base_uri' => 'https://qyapi.weixin.qq.com/',
            'timeout' => 2,
        ]);

        $response = $client->post('cgi-bin/webhook/send', [
            RequestOptions::QUERY => [
                'key' => $key,
            ],
            RequestOptions::JSON => $body,
        ]);

        $ret = Json::decode((string) $response->getBody());

        return ($ret['errcode'] ?? false) === 0;
    }
}
