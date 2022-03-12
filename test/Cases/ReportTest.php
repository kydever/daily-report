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
use function Han\Utils\date_load;

/**
 * @internal
 * @coversNothing
 */
class ReportTest extends HttpTestCase
{
    public function testCarbonFormat()
    {
        $carbon = date_load('10:00');

        $this->assertSame('10:00', $carbon->format('H:i'));
    }

    public function testAddItem()
    {
        $res = $this->json('/report/item', [
            'id' => 0,
            'project' => '日报',
            'module' => '测试',
            'summary' => '增加添加条目测试',
            'begin_time' => '10:00',
            'end_time' => '12:00',
        ]);

        $this->assertSame(0, $res['code']);
    }
}
