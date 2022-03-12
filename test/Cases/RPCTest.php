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
namespace HyperfTest\Cases;

use HyperfTest\HttpTestCase;
use PHPJieba\PHPJiebaInterface;

/**
 * @internal
 * @coversNothing
 */
class RPCTest extends HttpTestCase
{
    public function testJieba()
    {
        $res = di()->get(PHPJiebaInterface::class)->cut('我是Hyperf开发组的一员');

        $this->assertSame(['我', '是', 'Hyperf', '开发', '组', '的', '一员'], $res);
    }
}
