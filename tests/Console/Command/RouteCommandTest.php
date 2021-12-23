<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Console\Command;

use Platine\Config\Config;
use Platine\Console\Application as ConsoleApp;
use Platine\Console\IO\Interactor;
use Platine\Framework\App\Application;
use Platine\Framework\Console\Command\RouteCommand;
use Platine\Route\Route;
use Platine\Route\RouteCollection;
use Platine\Route\Router;
use Platine\Test\Framework\Console\BaseCommandTestCase;
use Platine\Test\Framework\Fixture\MyServiceProvider;

/*
 * @group core
 * @group framework
 */
class RouteCommandTest extends BaseCommandTestCase
{
    public function testExecuteDefault(): void
    {
        $route = $this->getMockInstance(Route::class, [
            'getName' => 'foo',
            'getPattern' => '/foo',
            'getHandler' => 'MyHandler',
            'getMethods' => ['GET', 'POST'],
        ]);

        $routeCollection = $this->getMockInstance(RouteCollection::class, [
            'all' => [$route]
        ]);
        $provider = $this->getMockInstance(MyServiceProvider::class);
        $app = $this->getMockInstance(Application::class, [
            'getProviders' => [$provider]
        ]);
        $router = $this->getMockInstance(Router::class, [
            'routes' => $routeCollection
        ]);
        $writer = $this->getWriterInstance();
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer
        ]);
        $consoleApp = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                [
                    'routes',
                    [],
                    [
                        function (Router $r) {
                        }
                    ]
                ],
            ]
        ]);

        $o = new RouteCommand($app, $config, $router);
        $o->bind($consoleApp);
        $o->parse(['platine', 'route', '-l']);
        $this->assertEquals('route', $o->getName());
        $o->execute();
        $expected = 'ROUTE LIST

+------+----------+------+-----------+
| Name | Method   | Path | Handler   |
+------+----------+------+-----------+
| foo  | GET|POST | /foo | MyHandler |
+------+----------+------+-----------+

Command finished successfully
';
        $this->assertEquals($expected, $this->getConsoleOutputContent());
    }
}
