<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http\Middleware;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Http\Middleware\CsrfTokenMiddleware;
use Platine\Framework\Kernel\HttpKernel;
use Platine\Framework\Security\Csrf\CsrfManager;
use Platine\Http\ServerRequest;
use Platine\Route\Route;

/*
 * @group core
 * @group framework
 */
class CsrfTokenMiddlewareTest extends PlatineTestCase
{
    public function testProcessRouteNotMatch(): void
    {
        $manager = $this->getMockInstance(CsrfManager::class);
        $request = $this->getMockInstance(ServerRequest::class);
        $handler = $this->getMockInstance(HttpKernel::class);

        $o = new CsrfTokenMiddleware($manager);
        $res = $o->process($request, $handler);

        $this->assertEquals(0, $res->getStatusCode());
    }





    public function testProcessSuccess(): void
    {
        $route = $this->getMockInstance(Route::class, [
            'getPattern' => '/bar/foo'
        ]);
        $request = $this->getMockInstance(ServerRequest::class, [
            'getAttribute' => $route,
        ]);
        $handler = $this->getMockInstance(HttpKernel::class);

        $manager = $this->getMockInstance(CsrfManager::class, [
            'getToken' => 'token'
        ]);


        $o = new CsrfTokenMiddleware($manager);
        $this->expectMethodCallCount($request, 'withAttribute');

        $res = $o->process($request, $handler);
        $this->assertEquals(0, $res->getStatusCode());
    }
}
