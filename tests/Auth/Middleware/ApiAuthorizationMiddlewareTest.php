<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Auth\Middleware;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Auth\Authorization\DefaultAuthorization;
use Platine\Framework\Auth\Middleware\ApiAuthorizationMiddleware;
use Platine\Framework\Http\Response\RestResponse;
use Platine\Framework\Kernel\HttpKernel;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequest;
use Platine\Route\Route;

/*
 * @group core
 * @group framework
 */
class ApiAuthorizationMiddlewareTest extends PlatineTestCase
{
    public function testProcessRouteNotMatch(): void
    {
        $request = $this->getMockInstance(ServerRequest::class);
        $handler = $this->getMockInstance(HttpKernel::class);

        $authorization = $this->getMockInstance(DefaultAuthorization::class);

        $o = new ApiAuthorizationMiddleware($authorization);
        $res = $o->process($request, $handler);

        $this->assertEquals(0, $res->getStatusCode());
    }

    public function testProcessNoPermissionAttribute(): void
    {
        $route = $this->getMockInstance(Route::class, [
            'getAttribute' => '',
        ]);
        $request = $this->getMockInstance(ServerRequest::class, [
            'getAttribute' => $route
        ]);
        $handler = $this->getMockInstance(HttpKernel::class);

        $authorization = $this->getMockInstance(DefaultAuthorization::class);

        $o = new ApiAuthorizationMiddleware($authorization);
        $res = $o->process($request, $handler);

        $this->assertEquals(0, $res->getStatusCode());
    }

    public function testProcessNotAuthorized(): void
    {
        $route = $this->getMockInstance(Route::class, [
            'getAttribute' => 'user'
        ]);
        $request = $this->getMockInstance(ServerRequest::class, [
            'getAttribute' => $route
        ]);
        $handler = $this->getMockInstance(HttpKernel::class);

        $authorization = $this->getMockInstance(DefaultAuthorization::class, [
            'isGranted' => false
        ]);

        $o = new ApiAuthorizationMiddleware($authorization);
        $res = $o->process($request, $handler);

        $this->assertEquals(403, $res->getStatusCode());
        $this->assertInstanceOf(RestResponse::class, $res);
    }

    public function testProcessIsAuthorized(): void
    {
        $route = $this->getMockInstance(Route::class, [
            'getAttribute' => 'user'
        ]);
        $request = $this->getMockInstance(ServerRequest::class, [
            'getAttribute' => $route
        ]);
        $handler = $this->getMockInstance(HttpKernel::class);
        $handler->expects($this->exactly(1))
                ->method('handle');

        $authorization = $this->getMockInstance(DefaultAuthorization::class, [
            'isGranted' => true
        ]);

        $o = new ApiAuthorizationMiddleware($authorization);
        $res = $o->process($request, $handler);
        $this->assertInstanceOf(ResponseInterface::class, $res);
    }
}
