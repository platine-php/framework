<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http\Middleware;

use Exception;
use Platine\Config\Config;
use Platine\Cookie\CookieManager;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\App\Application;
use Platine\Framework\Http\Exception\HttpException;
use Platine\Framework\Http\Middleware\MaintenanceMiddleware;
use Platine\Framework\Http\Response\TemplateResponse;
use Platine\Framework\Http\RouteHelper;
use Platine\Framework\Kernel\HttpKernel;
use Platine\Http\ServerRequest;
use Platine\Http\Uri;
use Platine\Stdlib\Helper\Json;
use Platine\Template\Template;

use function Platine\Test\Framework\Fixture\getTestMaintenanceDriver;

/*
 * @group core
 * @group framework
 */
class MaintenanceMiddlewareTest extends PlatineTestCase
{
    public function testProcessExceptUrl(): void
    {
        $app = $this->getMockInstance(Application::class);

        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['maintenance.cookie.name', 'platine_maintenance', 'platine_maintenance'],
                ['maintenance.url_whitelist', [], ['/foo/*']],
            ]
        ]);

        $uri = $this->getMockInstance(Uri::class, [
            'getPath' => '/foo/bar',
        ]);
        $request = $this->getMockInstance(ServerRequest::class, [
            'getUri' => $uri,
        ]);
        $handler = $this->getMockInstance(HttpKernel::class);

        $o = new MaintenanceMiddleware($config, $app);
        $res = $o->process($request, $handler);

        $this->assertEquals(0, $res->getStatusCode());
    }

    public function testProcessApplicationIsOnline(): void
    {
        $app = $this->getMockInstance(Application::class);

        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['maintenance.cookie.name', 'platine_maintenance', 'platine_maintenance'],
                ['maintenance.url_whitelist', [], []],
            ]
        ]);

        $uri = $this->getMockInstance(Uri::class, [
            'getPath' => '/foo/bar',
        ]);
        $request = $this->getMockInstance(ServerRequest::class, [
            'getUri' => $uri,
        ]);
        $handler = $this->getMockInstance(HttpKernel::class);

        $o = new MaintenanceMiddleware($config, $app);
        $res = $o->process($request, $handler);

        $this->assertEquals(0, $res->getStatusCode());
    }

    public function testProcessThrowExceptionGetData(): void
    {
        $app = $this->getMockInstance(Application::class, [
            'isInMaintenance' => true,
            'maintenance' => getTestMaintenanceDriver(true, true, true),
        ]);

        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['maintenance.cookie.name', 'platine_maintenance', 'platine_maintenance'],
                ['maintenance.url_whitelist', [], []],
            ]
        ]);

        $uri = $this->getMockInstance(Uri::class, [
            'getPath' => '/foo/bar',
        ]);
        $request = $this->getMockInstance(ServerRequest::class, [
            'getUri' => $uri,
        ]);
        $handler = $this->getMockInstance(HttpKernel::class);

        $o = new MaintenanceMiddleware($config, $app);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Maintenance data error');
        $res = $o->process($request, $handler);
    }

    public function testProcessBypassRoute(): void
    {
        $app = $this->getMockInstanceMap(Application::class, [
            'isInMaintenance' => [[true]],
            'maintenance' => [[getTestMaintenanceDriver(false, true, false)]],
            'get' => [
                [
                    RouteHelper::class,
                    $this->getMockInstance(RouteHelper::class, [
                        'generateUrl' => '/foo/home'
                    ])
                ],
                [
                    CookieManager::class,
                    $this->getMockInstance(CookieManager::class, [

                    ])
                ],
             ]
        ]);

        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['maintenance.cookie.name', 'platine_maintenance', 'platine_maintenance'],
                ['maintenance.bypass_route', '', 'platine_maintenance'],
                ['maintenance.url_whitelist', [], []],
            ]
        ]);

        $uri = $this->getMockInstance(Uri::class, [
            'getPath' => '/08685bd7-594b-4ce1-9a6b-f5d168ecdb05',
        ]);
        $request = $this->getMockInstance(ServerRequest::class, [
            'getUri' => $uri,
        ]);
        $handler = $this->getMockInstance(HttpKernel::class);

        $o = new MaintenanceMiddleware($config, $app);

        $res = $o->process($request, $handler);
        $this->assertEquals(0, $res->getStatusCode());
    }

    public function testProcessUsingCookieBypassSuccess(): void
    {
        $app = $this->getMockInstanceMap(Application::class, [
            'isInMaintenance' => [[true]],
            'maintenance' => [[getTestMaintenanceDriver(false, true, false)]],
            'get' => [
                [
                    RouteHelper::class,
                    $this->getMockInstance(RouteHelper::class, [
                        'generateUrl' => '/foo/home'
                    ])
                ],
                [
                    CookieManager::class,
                    $this->getMockInstance(CookieManager::class, [

                    ])
                ],
             ]
        ]);

        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['maintenance.cookie.name', 'platine_maintenance', 'platine_maintenance'],
                ['maintenance.bypass_route', '', 'platine_maintenance'],
                ['maintenance.url_whitelist', [], []],
            ]
        ]);

        $uri = $this->getMockInstance(Uri::class, [
            'getPath' => '/foo/bar',
        ]);
        $request = $this->getMockInstance(ServerRequest::class, [
            'getUri' => $uri,
            'getCookieParams' => ['platine_maintenance' => $this->getCookieTestValue()],
        ]);
        $handler = $this->getMockInstance(HttpKernel::class);

        $o = new MaintenanceMiddleware($config, $app);

        $res = $o->process($request, $handler);
        $this->assertEquals(0, $res->getStatusCode());
    }

    public function testProcessThrowServiceUnavailable(): void
    {
        $app = $this->getMockInstanceMap(Application::class, [
            'isInMaintenance' => [[true]],
            'maintenance' => [[getTestMaintenanceDriver(false, true, false, ['secret', 'template'])]],
            'get' => [
                [
                    RouteHelper::class,
                    $this->getMockInstance(RouteHelper::class, [
                        'generateUrl' => '/foo/home'
                    ])
                ],
                [
                    CookieManager::class,
                    $this->getMockInstance(CookieManager::class, [

                    ])
                ],
             ]
        ]);

        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['maintenance.cookie.name', 'platine_maintenance', 'platine_maintenance'],
                ['maintenance.bypass_route', '', 'platine_maintenance'],
                ['maintenance.url_whitelist', [], []],
            ]
        ]);

        $uri = $this->getMockInstance(Uri::class, [
            'getPath' => '/foo/bar',
        ]);
        $request = $this->getMockInstance(ServerRequest::class, [
            'getUri' => $uri,
            'getCookieParams' => ['platine_maintenance' => $this->getCookieTestValue()],
        ]);
        $handler = $this->getMockInstance(HttpKernel::class);

        $o = new MaintenanceMiddleware($config, $app);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Please the system is upgrading');
        $res = $o->process($request, $handler);
    }

    public function testProcessThrowServiceUnavailableUsingTemplate(): void
    {
        $app = $this->getMockInstanceMap(Application::class, [
            'isInMaintenance' => [[true]],
            'maintenance' => [[getTestMaintenanceDriver(false, true, false, [])]],
            'get' => [
                [
                    RouteHelper::class,
                    $this->getMockInstance(RouteHelper::class, [
                        'generateUrl' => '/foo/home'
                    ])
                ],
                [
                    CookieManager::class,
                    $this->getMockInstance(CookieManager::class, [

                    ])
                ],
                [
                    Template::class,
                    $this->getMockInstance(Template::class, [

                    ])
                ],
             ]
        ]);

        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['maintenance.cookie.name', 'platine_maintenance', 'platine_maintenance'],
                ['maintenance.bypass_route', '', 'platine_maintenance'],
                ['maintenance.url_whitelist', [], []],
            ]
        ]);

        $uri = $this->getMockInstance(Uri::class, [
            'getPath' => '/foo/bar',
        ]);
        $request = $this->getMockInstance(ServerRequest::class, [
            'getUri' => $uri,
            'getCookieParams' => ['platine_maintenance' => null],
        ]);
        $handler = $this->getMockInstance(HttpKernel::class);

        $o = new MaintenanceMiddleware($config, $app);

        $res = $o->process($request, $handler);
        $this->assertInstanceOf(TemplateResponse::class, $res);
        $this->assertEquals(503, $res->getStatusCode());
    }

    protected function getCookieTestValue(): string
    {
        $secret = '08685bd7-594b-4ce1-9a6b-f5d168ecdb05';
        $expire = time() + 10000;

        $data = [
            'expire' => $expire,
            'hash' => hash_hmac('sha256', (string) $expire, $secret)
        ];
        return base64_encode(Json::encode($data));
    }
}
