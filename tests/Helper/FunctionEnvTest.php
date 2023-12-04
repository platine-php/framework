<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Helper;

use Platine\Dev\PlatineTestCase;

class FunctionEnvTest extends PlatineTestCase
{
    public function testDefault(): void
    {
        global $mock_getenv_to_foo;
        $mock_getenv_to_foo = true;
        $this->assertEquals('foo', env('getenv_key'));
    }
}
