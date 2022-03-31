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

use EasyWeChat\Kernel\Form\File;
use EasyWeChat\Kernel\Form\Form;
use HyperfTest\HttpTestCase;

/**
 * @internal
 * @coversNothing
 */
class ExampleTest extends HttpTestCase
{
    public function testExample()
    {
        $this->assertTrue(true);
    }

    public function testForm()
    {
        $options = Form::create([
            'media' => File::fromPath(BASE_PATH . '/.gitignore'),
        ])->toArray();

        $this->assertIsArray($options);
    }
}
