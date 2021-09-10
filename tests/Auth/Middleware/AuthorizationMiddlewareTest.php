<?php

declare(strict_types=1);

namespace Platine\Test\Auth\Middleware;

use Platine\Config\Config;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Auth\Authorization\SessionAuthorization;
use Platine\Framework\Auth\Middleware\AuthorizationMiddleware;
use Platine\Framework\Http\RouteHelper;
use Platine\Framework\Kernel\HttpKernel;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequest;
use Platine\Route\Route;

/*
 * @group core
 * @group framework
 */
class AuthorizationMiddlewareTest extends PlatineTestCase
{

    public function testProcessRouteNotMatch(): void
    {
        $request = $this->getMockInstance(ServerRequest::class);
        $handler = $this->getMockInstance(HttpKernel::class);

        $routeHelper = $this->getMockInstance(RouteHelper::class);
        $authorization = $this->getMockInstance(SessionAuthorization::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['', null, []]
            ]
        ]);

        $o = new AuthorizationMiddleware($authorization, $config, $routeHelper);
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

        $routeHelper = $this->getMockInstance(RouteHelper::class);
        $authorization = $this->getMockInstance(SessionAuthorization::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [

            ]
        ]);

        $o = new AuthorizationMiddleware($authorization, $config, $routeHelper);
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

        $routeHelper = $this->getMockInstance(RouteHelper::class);
        $authorization = $this->getMockInstance(SessionAuthorization::class, [
            'isGranted' => false
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['auth.authorization.unauthorized_route', null, 'home'],
            ]
        ]);

        $o = new AuthorizationMiddleware($authorization, $config, $routeHelper);
        $res = $o->process($request, $handler);

        $this->assertEquals(302, $res->getStatusCode());
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

        $routeHelper = $this->getMockInstance(RouteHelper::class);
        $authorization = $this->getMockInstance(SessionAuthorization::class, [
            'isGranted' => true
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['auth.authentication.url_whitelist', [], []],
            ]
        ]);

        $o = new AuthorizationMiddleware($authorization, $config, $routeHelper);
        $res = $o->process($request, $handler);
        $this->assertInstanceOf(ResponseInterface::class, $res);
    }
}
