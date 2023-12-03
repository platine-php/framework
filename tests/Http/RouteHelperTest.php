<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Http\RouteHelper;
use Platine\Route\Router;

/*
 * @group core
 * @group framework
 */
class RouteHelperTest extends PlatineTestCase
{
    public function testAll(): void
    {
        $router = $this->getMockInstance(Router::class, [
            'path' => '/foo/bar'
        ]);
        $o = new RouteHelper($router);

        $this->assertEquals('/foo/bar', $o->generateUrl('foo'));
    }
}
