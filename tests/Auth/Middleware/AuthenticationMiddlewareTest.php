<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Auth\Middleware;

use Platine\Config\Config;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Auth\Authentication\SessionAuthentication;
use Platine\Framework\Auth\Middleware\AuthenticationMiddleware;
use Platine\Framework\Http\RouteHelper;
use Platine\Framework\Kernel\HttpKernel;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequest;
use Platine\Route\Route;

/*
 * @group core
 * @group framework
 */
class AuthenticationMiddlewareTest extends PlatineTestCase
{

    public function testProcessRouteNotMatch(): void
    {
        $request = $this->getMockInstance(ServerRequest::class);
        $handler = $this->getMockInstance(HttpKernel::class);

        $routeHelper = $this->getMockInstance(RouteHelper::class);
        $authentication = $this->getMockInstance(SessionAuthentication::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['', null, []]
            ]
        ]);

        $o = new AuthenticationMiddleware($authentication, $config, $routeHelper);
        $res = $o->process($request, $handler);

        $this->assertEquals(0, $res->getStatusCode());
    }

    public function testProcessUrlIsWhiteList(): void
    {
        $route = $this->getMockInstance(Route::class, [
            'getPattern' => '/login'
        ]);
        $request = $this->getMockInstance(ServerRequest::class, [
            'getAttribute' => $route
        ]);
        $handler = $this->getMockInstance(HttpKernel::class);

        $routeHelper = $this->getMockInstance(RouteHelper::class);
        $authentication = $this->getMockInstance(SessionAuthentication::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['auth.authentication.url_whitelist', [], ['/login']],
            ]
        ]);

        $o = new AuthenticationMiddleware($authentication, $config, $routeHelper);
        $res = $o->process($request, $handler);

        $this->assertEquals(0, $res->getStatusCode());
    }

    public function testProcessNotAuthenticated(): void
    {
        $route = $this->getMockInstance(Route::class, [
            'getPattern' => '/user'
        ]);
        $request = $this->getMockInstance(ServerRequest::class, [
            'getAttribute' => $route
        ]);
        $handler = $this->getMockInstance(HttpKernel::class);

        $routeHelper = $this->getMockInstance(RouteHelper::class);
        $authentication = $this->getMockInstance(SessionAuthentication::class, [
            'isLogged' => false
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['auth.authentication.url_whitelist', [], []],
                ['auth.authentication.login_route', null, 'home'],
            ]
        ]);

        $o = new AuthenticationMiddleware($authentication, $config, $routeHelper);
        $res = $o->process($request, $handler);

        $this->assertEquals(302, $res->getStatusCode());
    }

    public function testProcessIsAuthenticated(): void
    {
        $route = $this->getMockInstance(Route::class, [
            'getPattern' => '/user'
        ]);
        $request = $this->getMockInstance(ServerRequest::class, [
            'getAttribute' => $route
        ]);
        $handler = $this->getMockInstance(HttpKernel::class);
        $handler->expects($this->exactly(1))
                ->method('handle');

        $routeHelper = $this->getMockInstance(RouteHelper::class);
        $authentication = $this->getMockInstance(SessionAuthentication::class, [
            'isLogged' => true
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['auth.authentication.url_whitelist', [], []],
            ]
        ]);

        $o = new AuthenticationMiddleware($authentication, $config, $routeHelper);
        $res = $o->process($request, $handler);
        $this->assertInstanceOf(ResponseInterface::class, $res);
    }
}
