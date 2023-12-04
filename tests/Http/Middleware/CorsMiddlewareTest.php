<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http\Middleware;

use Platine\Config\Config;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Http\Middleware\CorsMiddleware;
use Platine\Framework\Kernel\HttpKernel;
use Platine\Http\ServerRequest;
use Platine\Logger\Logger;
use Platine\Route\Route;
use Platine\Test\Framework\Fixture\MyResponse;

/*
 * @group core
 * @group framework
 */
class CorsMiddlewareTest extends PlatineTestCase
{
    public function testProcessRouteNotMatch(): void
    {
        $logger = $this->getMockInstance(Logger::class);
        $request = $this->getMockInstance(ServerRequest::class);
        $handler = $this->getMockInstance(HttpKernel::class);

        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['', null, []]
            ]
        ]);

        $o = new CorsMiddleware($logger, $config);
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

        $logger = $this->getMockInstance(Logger::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['security.cors.path', '/', '/api'],
            ]
        ]);

        $o = new CorsMiddleware($logger, $config);
        $res = $o->process($request, $handler);

        $this->assertEquals(0, $res->getStatusCode());
    }

    public function testProcessPreflight(): void
    {
        $route = $this->getMockInstance(Route::class, [
            'getPattern' => '/api/foo'
        ]);
        $request = $this->getMockInstanceMap(ServerRequest::class, [
            'getAttribute' => [
                [Route::class, null, $route],
            ],
            'getMethod' => [
                ['OPTIONS']
            ],
            'getHeaderLine' => [
                ['Access-Control-Request-Headers', ''],
                ['Origin', '*'],
            ],
        ]);
        $handler = $this->getMockInstance(HttpKernel::class);

        $logger = $this->getMockInstance(Logger::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['security.cors.path', '/', '/api'],
                ['security.cors.origins', ['*'], ['*']],
                ['security.cors.expose_headers', [], ['One', 'Two']],
                ['security.cors.allow_headers', [], ['Three', 'Four']],
                ['security.cors.max_age', 1800, 1800],
                ['security.cors.allow_credentials', false, true],
                ['security.cors.allow_methods', [], ['GET', 'PUT']],
            ]
        ]);

        $o = new CorsMiddleware($logger, $config);
        $res = $o->process($request, $handler);
        $this->assertEquals('*', $res->getHeaderLine('Access-Control-Allow-Origin'));
        $this->assertEquals('1800', $res->getHeaderLine('Access-Control-Max-Age'));
        $this->assertEquals('true', $res->getHeaderLine('Access-Control-Allow-Credential'));
        $this->assertEquals('GET, PUT', $res->getHeaderLine('Access-Control-Allow-Methods'));
        $this->assertEquals('Three, Four', $res->getHeaderLine('Access-Control-Allow-Headers'));
        $this->assertEquals(204, $res->getStatusCode());
    }

    public function testProcessNotPreflight(): void
    {
        $response = new MyResponse();
        $route = $this->getMockInstance(Route::class, [
            'getPattern' => '/api/foo'
        ]);
        $request = $this->getMockInstanceMap(ServerRequest::class, [
            'getAttribute' => [
                [Route::class, null, $route],
            ],
            'getMethod' => [
                ['POST']
            ],
            'getHeaderLine' => [
                ['Access-Control-Request-Headers', ''],
                ['Origin', '*'],
            ],
        ]);
        $handler = $this->getMockInstance(HttpKernel::class, [
            'handle' => $response
        ]);

        $logger = $this->getMockInstance(Logger::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['security.cors.path', '/', '/api'],
                ['security.cors.origins', ['*'], ['*']],
                ['security.cors.expose_headers', [], ['One', 'Two']],
                ['security.cors.allow_credentials', false, true],
            ]
        ]);

        $o = new CorsMiddleware($logger, $config);
        $res = $o->process($request, $handler);
        $this->assertEquals('*', $res->getHeaderLine('Access-Control-Allow-Origin'));
        $this->assertEquals('true', $res->getHeaderLine('Access-Control-Allow-Credential'));
        $this->assertEquals('One, Two', $res->getHeaderLine('Access-Control-Expose-Headers'));
        $this->assertEquals(300, $res->getStatusCode());
    }


    public function testProcessPreflightUsingRequestCorsHeaders(): void
    {
        $route = $this->getMockInstance(Route::class, [
            'getPattern' => '/api/foo'
        ]);
        $request = $this->getMockInstanceMap(ServerRequest::class, [
            'getAttribute' => [
                [Route::class, null, $route],
            ],
            'getMethod' => [
                ['OPTIONS']
            ],
            'getHeaderLine' => [
                ['Access-Control-Request-Headers', 'Foo, Bar'],
                ['Origin', '*'],
            ],
        ]);
        $handler = $this->getMockInstance(HttpKernel::class);

        $logger = $this->getMockInstance(Logger::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['security.cors.path', '/', '/api'],
                ['security.cors.origins', ['*'], ['*']],
                ['security.cors.expose_headers', [], ['One', 'Two']],
                ['security.cors.allow_headers', [], []],
                ['security.cors.max_age', 1800, 1800],
                ['security.cors.allow_credentials', false, true],
                ['security.cors.allow_methods', [], ['GET', 'PUT']],
            ]
        ]);

        $o = new CorsMiddleware($logger, $config);
        $res = $o->process($request, $handler);
        $this->assertEquals('*', $res->getHeaderLine('Access-Control-Allow-Origin'));
        $this->assertEquals('1800', $res->getHeaderLine('Access-Control-Max-Age'));
        $this->assertEquals('true', $res->getHeaderLine('Access-Control-Allow-Credential'));
        $this->assertEquals('GET, PUT', $res->getHeaderLine('Access-Control-Allow-Methods'));
        $this->assertEquals('Foo, Bar', $res->getHeaderLine('Access-Control-Allow-Headers'));
        $this->assertEquals(204, $res->getStatusCode());
    }
}
