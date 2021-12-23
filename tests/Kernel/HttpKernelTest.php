<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Kernel;

use Platine\Config\Config;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\App\Application;
use Platine\Framework\Http\Emitter\EmitterInterface;
use Platine\Framework\Http\Emitter\ResponseEmitter;
use Platine\Framework\Http\Exception\HttpNotFoundException;
use Platine\Framework\Kernel\HttpKernel;
use Platine\Http\Handler\MiddlewareResolver;
use Platine\Http\ServerRequest;
use Platine\Http\ServerRequestInterface;
use Platine\Route\Router;
use Platine\Test\Framework\Fixture\MyMiddleware;
use Platine\Test\Framework\Fixture\MyRequestHandle;
use Platine\Test\Framework\Fixture\MyServiceProvider;

/*
 * @group core
 * @group framework
 */
class HttpKernelTest extends PlatineTestCase
{
    public function testUse(): void
    {
        $router = $this->getMockInstance(Router::class);
        $middlewareResolver = $this->getMockInstance(MiddlewareResolver::class);
        $middlewareResolver->expects($this->exactly(1))
                ->method('resolve');
        $app = $this->getMockInstance(Application::class);

        $o = new HttpKernel($app, $router, $middlewareResolver);
        $o->use(MyMiddleware::class);
    }

    public function testRunCustomRequest(): void
    {
        $provider = $this->getMockInstance(MyServiceProvider::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['routes', [], [function (Router $r) {
                    $r->get('/foo', MyRequestHandle::class);
                }]],
                ['middlewares', [], [MyMiddleware::class]],
            ]
        ]);
        $emiter = $this->getMockInstance(ResponseEmitter::class);
        $request = $this->getMockInstance(ServerRequest::class);
        $router = $this->getMockInstance(Router::class);
        $middlewareResolver = $this->getMockInstance(MiddlewareResolver::class);
        $app = $this->getMockInstanceMap(Application::class, [
            'get' => [
                [EmitterInterface::class, $emiter],
                [Config::class, $config],
                [ServerRequestInterface::class, $request],
            ],
            'getProviders' => [
                [[$provider]]
            ]
        ]);
        $app->expects($this->exactly(2))
                ->method('instance');
        $app->expects($this->exactly(5))
                ->method('get');
        $o = new HttpKernel($app, $router, $middlewareResolver);
        $o->run($request);
    }

    public function testRun404(): void
    {
        global $mock_current_to_false;
        $mock_current_to_false = true;

        $provider = $this->getMockInstance(MyServiceProvider::class);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['routes', [], [function (Router $r) {
                    $r->get('/foo', MyRequestHandle::class);
                }]],
                ['middlewares', [], [MyMiddleware::class]],
            ]
        ]);
        $emiter = $this->getMockInstance(ResponseEmitter::class);
        $request = $this->getMockInstance(ServerRequest::class);
        $router = $this->getMockInstance(Router::class);
        $middlewareResolver = $this->getMockInstance(MiddlewareResolver::class);
        $app = $this->getMockInstanceMap(Application::class, [
            'get' => [
                [EmitterInterface::class, $emiter],
                [Config::class, $config],
                [ServerRequestInterface::class, $request],
            ],
            'getProviders' => [
                [[$provider]]
            ]
        ]);
        $app->expects($this->exactly(2))
                ->method('instance');
        $app->expects($this->exactly(5))
                ->method('get');
        $o = new HttpKernel($app, $router, $middlewareResolver);

        $this->expectException(HttpNotFoundException::class);
        $o->run($request);
    }

    public function testDetermineBasePathUsingApp(): void
    {

        $router = $this->getMockInstance(Router::class);
        $middlewareResolver = $this->getMockInstance(MiddlewareResolver::class);
        $app = $this->getMockInstanceMap(Application::class, [
            'getBasePath' => [
                ['/appbasepath']
            ]
        ]);

        $o = new HttpKernel($app, $router, $middlewareResolver);

        $res = $this->runPrivateProtectedMethod($o, 'determineBasePath');
        $this->assertEquals($res, '/appbasepath');
    }

    public function testDetermineBasePathUsingConfig(): void
    {
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['app.base_path', null, '/configbasepath']
            ]
        ]);
        $router = $this->getMockInstance(Router::class);
        $middlewareResolver = $this->getMockInstance(MiddlewareResolver::class);
        $app = $this->getMockInstanceMap(Application::class, [
            'getBasePath' => [
                ['']
            ],
            'get' => [
                [Config::class, $config],
            ],
        ]);

        $o = new HttpKernel($app, $router, $middlewareResolver);

        $res = $this->runPrivateProtectedMethod($o, 'determineBasePath');
        $this->assertEquals($res, '/configbasepath');
    }

    public function testDetermineBasePathAuto(): void
    {
        $request = $this->getMockInstance(ServerRequest::class, [
            'getServerParams' => [
                'SCRIPT_NAME' => '/path/to/index.php'
            ]
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['app.base_path', null, '']
            ]
        ]);
        $router = $this->getMockInstance(Router::class);
        $middlewareResolver = $this->getMockInstance(MiddlewareResolver::class);
        $app = $this->getMockInstanceMap(Application::class, [
            'getBasePath' => [
                ['']
            ],
            'get' => [
                [Config::class, $config],
                [ServerRequestInterface::class, $request],
            ],
        ]);

        $o = new HttpKernel($app, $router, $middlewareResolver);

        $res = $this->runPrivateProtectedMethod($o, 'determineBasePath');
        $this->assertEquals($res, '/path/to');
    }
}
