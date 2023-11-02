<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http\Middleware;

use Platine\Config\Config;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Http\Middleware\CsrfMiddleware;
use Platine\Framework\Kernel\HttpKernel;
use Platine\Framework\Security\Csrf\CsrfManager;
use Platine\Http\ServerRequest;
use Platine\Lang\Lang;
use Platine\Logger\Logger;
use Platine\Route\Route;

/*
 * @group core
 * @group framework
 */
class CsrfMiddlewareTest extends PlatineTestCase
{
    public function testProcessRouteNotMatch(): void
    {
        $manager = $this->getMockInstance(CsrfManager::class);
        $lang = $this->getMockInstance(Lang::class);
        $logger = $this->getMockInstance(Logger::class);
        $request = $this->getMockInstance(ServerRequest::class);
        $handler = $this->getMockInstance(HttpKernel::class);

        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['', null, []]
            ]
        ]);

        $o = new CsrfMiddleware($logger, $lang, $config, $manager);
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

        $manager = $this->getMockInstance(CsrfManager::class);
        $lang = $this->getMockInstance(Lang::class);
        $logger = $this->getMockInstance(Logger::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['security.csrf.http_methods', [], []],
            ]
        ]);

        $o = new CsrfMiddleware($logger, $lang, $config, $manager);
        $res = $o->process($request, $handler);

        $this->assertEquals(0, $res->getStatusCode());
    }

    public function testProcessUrlWhitelist(): void
    {
        $route = $this->getMockInstance(Route::class, [
            'getPattern' => '/api/foo',
            'getName' => 'api',
        ]);
        $request = $this->getMockInstance(ServerRequest::class, [
            'getAttribute' => $route,
            'getMethod' => 'POST',
        ]);
        $handler = $this->getMockInstance(HttpKernel::class);

        $manager = $this->getMockInstance(CsrfManager::class);
        $lang = $this->getMockInstance(Lang::class);
        $logger = $this->getMockInstance(Logger::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['security.csrf.http_methods', [], ['POST']],
                ['security.csrf.url_whitelist', [], ['api']],
            ]
        ]);

        $o = new CsrfMiddleware($logger, $lang, $config, $manager);
        $res = $o->process($request, $handler);

        $this->assertEquals(0, $res->getStatusCode());
    }

    public function testProcessCsrfManagerInvalid(): void
    {
        $route = $this->getMockInstance(Route::class, [
            'getPattern' => '/bar/foo'
        ]);
        $request = $this->getMockInstance(ServerRequest::class, [
            'getAttribute' => $route,
            'getMethod' => 'POST',
        ]);
        $handler = $this->getMockInstance(HttpKernel::class);

        $manager = $this->getMockInstance(CsrfManager::class, [
            'validate' => false
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

        $o = new CsrfMiddleware($logger, $lang, $config, $manager);
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

        $manager = $this->getMockInstance(CsrfManager::class, [
            'validate' => true
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

        $o = new CsrfMiddleware($logger, $lang, $config, $manager);
        $res = $o->process($request, $handler);

        $this->assertEquals(0, $res->getStatusCode());
    }

    public function testProcessSuccessUsingGetMethod(): void
    {
        $route = $this->getMockInstance(Route::class, [
            'getPattern' => '/bar/foo',
            'getAttribute' => true,
        ]);
        $request = $this->getMockInstance(ServerRequest::class, [
            'getAttribute' => $route,
            'getMethod' => 'GET',
            'getQueryParams' => ['csrf_key' => 'foo'],
        ]);
        $handler = $this->getMockInstance(HttpKernel::class);

        $manager = $this->getMockInstance(CsrfManager::class, [
            'validate' => true
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

        $o = new CsrfMiddleware($logger, $lang, $config, $manager);
        $res = $o->process($request, $handler);

        $this->assertEquals(0, $res->getStatusCode());
    }
}
