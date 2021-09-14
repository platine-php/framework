<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Helper;

use Platine\Container\ContainerInterface;
use Platine\Dev\PlatineTestCase;
use Platine\Http\Handler\MiddlewareResolverInterface;

class FunctionAppTest extends PlatineTestCase
{

    public function testDefault(): void
    {
        $o = app();
        $this->assertInstanceOf(ContainerInterface::class, $o);
    }

    public function testWithParam(): void
    {
        $o = app(MiddlewareResolverInterface::class);
        $this->assertInstanceOf(MiddlewareResolverInterface::class, $o);
    }
}
