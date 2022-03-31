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

    public function testAddAndDeleteItem()
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

        $id = $res['data']['id'];

        $res = $this->get('/report');

        $this->assertSame(0, $res['code']);

        $res = $this->delete('/report/item/' . $id);

        $this->assertSame(0, $res['code']);
    }

    public function testItems()
    {
        $res = $this->get('report/22f15490bbc0c8278af08c9662b8d495/item');
        $this->assertNotEmpty($res);
        $this->assertSame(0, $res['code']);
    }
}
