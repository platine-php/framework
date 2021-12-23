<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http\Middleware;

use Platine\Config\Config;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Http\Middleware\CorsMiddleware;
use Platine\Framework\Http\Middleware\CsrfMiddleware;
use Platine\Framework\Kernel\HttpKernel;
use Platine\Http\ServerRequest;
use Platine\Lang\Lang;
use Platine\Logger\Logger;
use Platine\Route\Route;
use Platine\Session\Session;
use Platine\Test\Framework\Fixture\MyResponse;

/*
 * @group core
 * @group framework
 */
class CsrfMiddlewareTest extends PlatineTestCase
{
    public function testProcessRouteNotMatch(): void
    {
        $session = $this->getMockInstance(Session::class);
        $lang = $this->getMockInstance(Lang::class);
        $logger = $this->getMockInstance(Logger::class);
        $request = $this->getMockInstance(ServerRequest::class);
        $handler = $this->getMockInstance(HttpKernel::class);

        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['', null, []]
            ]
        ]);

        $o = new CsrfMiddleware($logger, $lang, $config, $session);
        $res = $o->process($request, $handler);

        $this->assertEquals(0, $res->getStatusCode());
    }

    public function testProcessMethodNotMatch(): void
    {
        $route = $this->getMockInstance(Route::class, [
            'getPattern' => '/foo'
        ]);
        $request = $this->getMockInstance(ServerRequest::class, [
            'getAttribute' => $route,
            'getMethod' => 'GET',
        ]);
        $handler = $this->getMockInstance(HttpKernel::class);

        $session = $this->getMockInstance(Session::class);
        $lang = $this->getMockInstance(Lang::class);
        $logger = $this->getMockInstance(Logger::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['security.csrf.http_methods', [], []],
            ]
        ]);

        $o = new CsrfMiddleware($logger, $lang, $config, $session);
        $res = $o->process($request, $handler);

        $this->assertEquals(0, $res->getStatusCode());
    }

    public function testProcessUrlWhitelist(): void
    {
        $route = $this->getMockInstance(Route::class, [
            'getPattern' => '/api/foo'
        ]);
        $request = $this->getMockInstance(ServerRequest::class, [
            'getAttribute' => $route,
            'getMethod' => 'POST',
        ]);
        $handler = $this->getMockInstance(HttpKernel::class);

        $session = $this->getMockInstance(Session::class);
        $lang = $this->getMockInstance(Lang::class);
        $logger = $this->getMockInstance(Logger::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['security.csrf.http_methods', [], ['POST']],
                ['security.csrf.url_whitelist', [], ['/api']],
            ]
        ]);

        $o = new CsrfMiddleware($logger, $lang, $config, $session);
        $res = $o->process($request, $handler);

        $this->assertEquals(0, $res->getStatusCode());
    }

    public function testProcessSessionInvalid(): void
    {
        $route = $this->getMockInstance(Route::class, [
            'getPattern' => '/bar/foo'
        ]);
        $request = $this->getMockInstance(ServerRequest::class, [
            'getAttribute' => $route,
            'getMethod' => 'POST',
        ]);
        $handler = $this->getMockInstance(HttpKernel::class);

        $session = $this->getMockInstanceMap(Session::class, [
            'get' => [
                ['csrf_data.expire', null, null],
                ['csrf_data.value', null, null],
            ]
        ]);
        $lang = $this->getMockInstance(Lang::class);
        $logger = $this->getMockInstance(Logger::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['security.csrf.http_methods', [], ['POST']],
                ['security.csrf.url_whitelist', [], ['/api']],
                ['security.csrf.key', '', 'csrf_key'],
            ]
        ]);

        $o = new CsrfMiddleware($logger, $lang, $config, $session);
        $res = $o->process($request, $handler);

        $this->assertEquals(401, $res->getStatusCode());
    }

    public function testProcessTokenInvalid(): void
    {
        $route = $this->getMockInstance(Route::class, [
            'getPattern' => '/bar/foo'
        ]);
        $request = $this->getMockInstance(ServerRequest::class, [
            'getAttribute' => $route,
            'getMethod' => 'POST',
            'getParsedBody' => ['csrf_key' => 'bar'],
        ]);
        $handler = $this->getMockInstance(HttpKernel::class);

        $session = $this->getMockInstanceMap(Session::class, [
            'get' => [
                ['csrf_data.expire', null, time() + 10000],
                ['csrf_data.value', null, 'foo'],
            ]
        ]);
        $lang = $this->getMockInstance(Lang::class);
        $logger = $this->getMockInstance(Logger::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['security.csrf.http_methods', [], ['POST']],
                ['security.csrf.url_whitelist', [], ['/api']],
                ['security.csrf.key', '', 'csrf_key'],
            ]
        ]);

        $o = new CsrfMiddleware($logger, $lang, $config, $session);
        $res = $o->process($request, $handler);

        $this->assertEquals(401, $res->getStatusCode());
    }

    public function testProcessSuccess(): void
    {
        $route = $this->getMockInstance(Route::class, [
            'getPattern' => '/bar/foo'
        ]);
        $request = $this->getMockInstance(ServerRequest::class, [
            'getAttribute' => $route,
            'getMethod' => 'POST',
            'getParsedBody' => ['csrf_key' => 'foo'],
        ]);
        $handler = $this->getMockInstance(HttpKernel::class);

        $session = $this->getMockInstanceMap(Session::class, [
            'get' => [
                ['csrf_data.expire', null, time() + 10000],
                ['csrf_data.value', null, 'foo'],
            ]
        ]);
        $lang = $this->getMockInstance(Lang::class);
        $logger = $this->getMockInstance(Logger::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['security.csrf.http_methods', [], ['POST']],
                ['security.csrf.url_whitelist', [], ['/api']],
                ['security.csrf.key', '', 'csrf_key'],
            ]
        ]);

        $o = new CsrfMiddleware($logger, $lang, $config, $session);
        $res = $o->process($request, $handler);

        $this->assertEquals(0, $res->getStatusCode());
    }
}
