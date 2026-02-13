<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http\Action;

use Platine\Config\Config;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Helper\ActionHelper;
use Platine\Framework\Helper\Sidebar;
use Platine\Framework\Http\Action\BaseAction;
use Platine\Framework\Http\Response\RedirectResponse;
use Platine\Framework\Http\Response\TemplateResponse;
use Platine\Framework\Http\RouteHelper;
use Platine\Framework\Security\SecurityPolicy;
use Platine\Http\ServerRequest;
use Platine\Logger\Logger;
use Platine\Test\Framework\Fixture\MyBaseAction;

class BaseActionTest extends PlatineTestCase
{
    public function testConstruct(): void
    {
        $logger = $this->getMockInstance(Logger::class);
        $this->setClassCreateObjectMaps(ActionHelper::class, [
            'logger' => $logger,
        ]);
        $actionHelper = $this->createObject(ActionHelper::class);

        $o = new MyBaseAction($actionHelper);
        $this->assertInstanceOf(MyBaseAction::class, $o);
    }


    public function testHandleBypassPagination(): void
    {
        $this->handle(true);
    }

    public function testHandleDoNotBypassPagination(): void
    {
        $this->handle(false);
    }


    public function testRedirect(): void
    {
        global $mock_app_httpaction_to_instance;
        $mock_app_httpaction_to_instance = true;

        $logger = $this->getMockInstance(Logger::class);
        $routeHelper = $this->getMockInstance(RouteHelper::class, [
            'generateUrl' => '/user/create',
        ]);
        $this->setClassCreateObjectMaps(ActionHelper::class, [
            'logger' => $logger,
            'routeHelper' => $routeHelper,
        ]);
        $actionHelper = $this->createObject(ActionHelper::class);

        $o = new MyBaseAction($actionHelper);

        $this->expectMethodCallCount($routeHelper, 'generateUrl');
        $resp = $this->runPrivateProtectedMethod($o, 'redirect', ['user_create', [], ['foo' => 'bar']]);
        $this->assertInstanceOf(RedirectResponse::class, $resp);
        $this->assertEquals(302, $resp->getStatusCode());
        $this->assertEquals('/user/create?foo=bar', $resp->getHeaderLine('location'));
    }

    public function testRedirectBackToOriginMissingRoute(): void
    {
        $this->redirectBackToOrigin('0', null);
    }

    public function testRedirectBackToOriginIdNotProvided(): void
    {
        $this->redirectBackToOrigin('0', 'user_detail');
    }

    public function testRedirectBackToOriginIdProvided(): void
    {
        $this->redirectBackToOrigin('1', 'user_detail');
    }


    protected function handle(bool $bypassPagination = false): void
    {
        global $mock_app_httpaction_to_instance;
        $mock_app_httpaction_to_instance = true;

        $sidebar = $this->getMockInstance(Sidebar::class, [
            'render' => 'my sidebar'
        ]);

        $logger = $this->getMockInstance(Logger::class);
        $request = $this->getMockInstanceMap(ServerRequest::class, [
            'getAttribute' => [
                [
                    SecurityPolicy::class, null, [
                        'nonces' => [
                            'style' => 'stylenonces',
                            'script' => 'scriptnonces',
                        ],
                    ],
                ],
                ['csrf_token', null, 'mytoken'],
            ],
            'getQueryParams' => [
                [[
                    'fields' => 'name,status',
                    'sort' => 'name:desc,status',
                    'page' => 1,
                    'limit' => 200,
                    'status' => 'Y',
                    'start_date' => '2025-09-01',
                    'end_date' => '2025-09-30',
                    'permissions' => [5,7],
                    'multi' => [5,7, null],
                    'all' => $bypassPagination ? 1 : 0
                ]]
            ],
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['pagination.max_limit', 1000, 1000],
            ],
        ]);

        $this->setClassCreateObjectMaps(ActionHelper::class, [
            'logger' => $logger,
            'sidebar' => $sidebar,
            'config' => $config,
        ]);
        $actionHelper = $this->createObject(ActionHelper::class);

        $o = new MyBaseAction($actionHelper);
        $this->expectMethodCallCount($sidebar, 'render');
        $this->expectMethodCallCount($sidebar, 'add');
        $this->expectMethodCallCount($request, 'getMethod');
        $this->expectMethodCallCount($request, 'getQueryParams');
        $this->expectMethodCallCount($request, 'getAttribute', 2);
        $resp = $o->handle($request);
        $this->assertInstanceOf(TemplateResponse::class, $resp);
        $this->assertEquals(200, $resp->getStatusCode());

        $this->assertEquals(
            $bypassPagination ? null : 200,
            $this->getPropertyValue(BaseAction::class, $o, 'limit')
        );
        $this->assertEquals('foo_view', $this->getPropertyValue(BaseAction::class, $o, 'viewName'));
        $this->assertEquals(
            $bypassPagination ? null : 1,
            $this->getPropertyValue(BaseAction::class, $o, 'page')
        );

        $this->assertEquals(
            $bypassPagination ? true : false,
            $this->getPropertyValue(BaseAction::class, $o, 'all')
        );

        $this->assertEquals(
            ['permissions' => [5,7], 'status' => 'Y', 'multi' => [5, 7]],
            $this->getPropertyValue(BaseAction::class, $o, 'filters')
        );
    }

    protected function redirectBackToOrigin(string $originId = '0', ?string $originRoute = null): void
    {
        global $mock_app_httpaction_to_instance;
        $mock_app_httpaction_to_instance = true;

        $logger = $this->getMockInstance(Logger::class);
        $routeHelper = $this->getMockInstance(RouteHelper::class, [
            'generateUrl' => '/user/detail',
        ]);
        $request = $this->getMockInstanceMap(ServerRequest::class, [
            'getQueryParams' => [
                [[
                    'origin_route' => $originRoute,
                    'origin_id' => $originId,
                ]]
            ],
        ]);
        $this->setClassCreateObjectMaps(ActionHelper::class, [
            'logger' => $logger,
            'routeHelper' => $routeHelper,
        ]);
        $actionHelper = $this->createObject(ActionHelper::class);

        $o = new MyBaseAction($actionHelper);
        $o->handle($request);
        $resp = $this->runPrivateProtectedMethod($o, 'redirectBackToOrigin', []);
        if ($originRoute !== null) {
            $this->assertInstanceOf(RedirectResponse::class, $resp);
            $this->assertEquals(302, $resp->getStatusCode());
            $this->assertEquals('/user/detail', $resp->getHeaderLine('location'));
        } else {
            $this->assertNull($resp);
        }
    }
}
