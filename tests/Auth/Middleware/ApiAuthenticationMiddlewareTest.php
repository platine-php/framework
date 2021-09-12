<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Auth\Middleware;

use Platine\Config\Config;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Auth\Authentication\JWTAuthentication;
use Platine\Framework\Auth\Middleware\ApiAuthenticationMiddleware;
use Platine\Framework\Kernel\HttpKernel;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequest;
use Platine\Route\Route;

/*
 * @group core
 * @group framework
 */
class ApiAuthenticationMiddlewareTest extends PlatineTestCase
{

    public function testProcessRouteNotMatch(): void
    {
        $request = $this->getMockInstance(ServerRequest::class);
        $handler = $this->getMockInstance(HttpKernel::class);

        $authentication = $this->getMockInstance(JWTAuthentication::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['', null, []]
            ]
        ]);

        $o = new ApiAuthenticationMiddleware($authentication, $config);
        $res = $o->process($request, $handler);

        $this->assertEquals(0, $res->getStatusCode());
    }

    public function testProcessPathNotMatch(): void
    {
        $route = $this->getMockInstance(Route::class, [
            'getPattern' => '/foo'
        ]);
        $request = $this->getMockInstance(ServerRequest::class, [
            'getAttribute' => $route
        ]);
        $handler = $this->getMockInstance(HttpKernel::class);

        $authentication = $this->getMockInstance(JWTAuthentication::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['api.auth.path', '/', '/api'],
            ]
        ]);

        $o = new ApiAuthenticationMiddleware($authentication, $config);
        $res = $o->process($request, $handler);

        $this->assertEquals(0, $res->getStatusCode());
    }

    public function testProcessUrlIsWhiteList(): void
    {
        $route = $this->getMockInstance(Route::class, [
            'getPattern' => '/api'
        ]);
        $request = $this->getMockInstance(ServerRequest::class, [
            'getAttribute' => $route
        ]);
        $handler = $this->getMockInstance(HttpKernel::class);

        $authentication = $this->getMockInstance(JWTAuthentication::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['api.auth.path', '/', '/api'],
                ['api.auth.url_whitelist', [], ['/api']],
            ]
        ]);

        $o = new ApiAuthenticationMiddleware($authentication, $config);
        $res = $o->process($request, $handler);

        $this->assertEquals(0, $res->getStatusCode());
    }

    public function testProcessNotAuthenticated(): void
    {
        $route = $this->getMockInstance(Route::class, [
            'getPattern' => '/api'
        ]);
        $request = $this->getMockInstance(ServerRequest::class, [
            'getAttribute' => $route
        ]);
        $handler = $this->getMockInstance(HttpKernel::class);

        $authentication = $this->getMockInstance(JWTAuthentication::class, [
            'isAuthenticated' => false
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['api.auth.path', '/', '/api'],
                ['api.auth.url_whitelist', [], []],
            ]
        ]);

        $o = new ApiAuthenticationMiddleware($authentication, $config);
        $res = $o->process($request, $handler);

        $this->assertEquals(401, $res->getStatusCode());
    }

    public function testProcessIsAuthenticated(): void
    {
        $route = $this->getMockInstance(Route::class, [
            'getPattern' => '/api'
        ]);
        $request = $this->getMockInstance(ServerRequest::class, [
            'getAttribute' => $route
        ]);
        $handler = $this->getMockInstance(HttpKernel::class);
        $handler->expects($this->exactly(1))
                ->method('handle');
        $authentication = $this->getMockInstance(JWTAuthentication::class, [
            'isAuthenticated' => true
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['api.auth.path', '/', '/api'],
                ['api.auth.url_whitelist', [], []],
            ]
        ]);

        $o = new ApiAuthenticationMiddleware($authentication, $config);
        $res = $o->process($request, $handler);
        $this->assertInstanceOf(ResponseInterface::class, $res);
    }
}
