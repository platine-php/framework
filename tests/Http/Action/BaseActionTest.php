<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http\Action;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Helper\ActionHelper;
use Platine\Framework\Helper\Sidebar;
use Platine\Framework\Http\Action\BaseAction;
use Platine\Framework\Http\Response\RedirectResponse;
use Platine\Framework\Http\Response\RestResponse;
use Platine\Framework\Http\Response\TemplateResponse;
use Platine\Framework\Http\RouteHelper;
use Platine\Framework\Security\SecurityPolicy;
use Platine\Http\ServerRequest;
use Platine\Lang\Lang;
use Platine\Logger\Logger;
use Platine\Pagination\Pagination;
use Platine\Test\Framework\Fixture\MyBaseAction;
use Platine\Test\Framework\Fixture\MyBaseAction2;

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

        $o = new MyBaseAction2($actionHelper);

        $this->expectMethodCallCount($routeHelper, 'generateUrl');
        $resp = $this->runPrivateProtectedMethod($o, 'redirect', ['user_create', [], ['foo' => 'bar']]);
        $this->assertInstanceOf(RedirectResponse::class, $resp);
        $this->assertEquals(302, $resp->getStatusCode());
        $this->assertEquals('/user/create?foo=bar', $resp->getHeaderLine('location'));

        // Why put this here?
        $this->assertEmpty($this->runPrivateProtectedMethod($o, 'getIgnoreDateFilters', []));
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

    public function testParseForeignConstraintErrorMessageMySQL(): void
    {
        global $mock_app_httpaction_to_instance;
        $mock_app_httpaction_to_instance = true;

        $logger = $this->getMockInstance(Logger::class);
        $lang = $this->getMockInstance(Lang::class, [
            'tr' => 'mysql error lang',
        ]);
        $this->setClassCreateObjectMaps(ActionHelper::class, [
            'logger' => $logger,
            'lang' => $lang,
        ]);
        $actionHelper = $this->createObject(ActionHelper::class);

        $o = new MyBaseAction($actionHelper);

        $error = $this->runPrivateProtectedMethod(
            $o,
            'parseForeignConstraintErrorMessage',
            [
                'SQLSTATE[23000]: Integrity constraint violation: 1217 Cannot delete or update a parent row'
            ]
        );
        $this->assertEquals('mysql error lang', $error);
    }

    public function testParseForeignConstraintErrorMessageMariaDB(): void
    {
        global $mock_app_httpaction_to_instance;
        $mock_app_httpaction_to_instance = true;

        $logger = $this->getMockInstance(Logger::class);
        $lang = $this->getMockInstance(Lang::class, [
            'tr' => 'mariadb error lang',
        ]);
        $this->setClassCreateObjectMaps(ActionHelper::class, [
            'logger' => $logger,
            'lang' => $lang,
        ]);
        $actionHelper = $this->createObject(ActionHelper::class);

        $o = new MyBaseAction($actionHelper);

        $error = $this->runPrivateProtectedMethod(
            $o,
            'parseForeignConstraintErrorMessage',
            [
                'SQLSTATE[23000]: Integrity constraint violation: 1451 Cannot delete or update a parent row'
            ]
        );
        $this->assertEquals('mariadb error lang', $error);
    }

    public function testRestResponse(): void
    {
        global $mock_time_to_1000;
        $mock_time_to_1000 = true;

        $logger = $this->getMockInstance(Logger::class);
        $pagination = $this->getMockInstance(Pagination::class, [
            'getTotalItems' => 16,
            'getInfo' => ['page' => 1],
        ]);
        $this->setClassCreateObjectMaps(ActionHelper::class, [
            'logger' => $logger,
            'pagination' => $pagination,
        ]);
        $actionHelper = $this->createObject(ActionHelper::class);

        $o = new MyBaseAction($actionHelper);
        /** @var RestResponse $resp */
        $resp = $this->runPrivateProtectedMethod(
            $o,
            'restResponse',
            [['foo' => 'bar'], 201, 0]
        );
        $this->assertInstanceOf(RestResponse::class, $resp);
        $this->assertEquals(201, $resp->getStatusCode());
        $this->assertEquals('Created', $resp->getReasonPhrase());
        $this->assertEquals(87, $resp->getBody()->getSize());
        $resp->getBody()->rewind();
        $this->assertEquals(
            '{"success":true,"timestamp":1000,"code":0,"data":{"foo":"bar"},"pagination":{"page":1}}',
            $resp->getBody()->getContents()
        );
    }

    public function testRestErrorResponse(): void
    {
        global $mock_time_to_1000;
        $mock_time_to_1000 = true;

        $logger = $this->getMockInstance(Logger::class);
        $this->setClassCreateObjectMaps(ActionHelper::class, [
            'logger' => $logger,
        ]);
        $actionHelper = $this->createObject(ActionHelper::class);

        $o = new MyBaseAction($actionHelper);
        /** @var RestResponse $resp */
        $resp = $this->runPrivateProtectedMethod(
            $o,
            'restErrorResponse',
            ['Error response']
        );
        $this->assertInstanceOf(RestResponse::class, $resp);
        $this->assertEquals(401, $resp->getStatusCode());
        $this->assertEquals(83, $resp->getBody()->getSize());
        $resp->getBody()->rewind();
        $this->assertEquals(
            '{"success":false,"timestamp":1000,"code":4000,"message":"Error response","data":[]}',
            $resp->getBody()->getContents()
        );
    }

    public function testAllRestErrorResponse(): void
    {
        global $mock_time_to_1000;
        $mock_time_to_1000 = true;

        $logger = $this->getMockInstance(Logger::class);
        $lang = $this->getMockInstance(Lang::class, ['tr' => 'lang msg']);
        $this->setClassCreateObjectMaps(ActionHelper::class, [
            'logger' => $logger,
            'lang' => $lang,
        ]);
        $actionHelper = $this->createObject(ActionHelper::class);

        $o = new MyBaseAction($actionHelper);

        $resp1 = $this->runPrivateProtectedMethod(
            $o,
            'restServerErrorResponse',
            ['Server Error']
        );
        $this->assertEquals(500, $resp1->getStatusCode());
        $this->assertEquals(81, $resp1->getBody()->getSize());
        $resp1->getBody()->rewind();
        $this->assertEquals(
            '{"success":false,"timestamp":1000,"code":5000,"message":"Server Error","data":[]}',
            $resp1->getBody()->getContents()
        );

        //
        $resp2 = $this->runPrivateProtectedMethod(
            $o,
            'restBadRequestErrorResponse',
            ['Bad Error']
        );
        $this->assertEquals(400, $resp2->getStatusCode());
        $this->assertEquals(78, $resp2->getBody()->getSize());
        $resp2->getBody()->rewind();
        $this->assertEquals(
            '{"success":false,"timestamp":1000,"code":4000,"message":"Bad Error","data":[]}',
            $resp2->getBody()->getContents()
        );

        //
        $resp3 = $this->runPrivateProtectedMethod(
            $o,
            'restConflictErrorResponse',
            ['Conflict Error']
        );
        $this->assertEquals(409, $resp3->getStatusCode());
        $this->assertEquals(83, $resp3->getBody()->getSize());
        $resp3->getBody()->rewind();
        $this->assertEquals(
            '{"success":false,"timestamp":1000,"code":4090,"message":"Conflict Error","data":[]}',
            $resp3->getBody()->getContents()
        );

        //
        $resp4 = $this->runPrivateProtectedMethod(
            $o,
            'restNotFoundErrorResponse',
            ['Not Found Error']
        );
        $this->assertEquals(404, $resp4->getStatusCode());
        $this->assertEquals(84, $resp4->getBody()->getSize());
        $resp4->getBody()->rewind();
        $this->assertEquals(
            '{"success":false,"timestamp":1000,"code":4040,"message":"Not Found Error","data":[]}',
            $resp4->getBody()->getContents()
        );

        //
        $resp5 = $this->runPrivateProtectedMethod(
            $o,
            'restFormValidationErrorResponse',
            [['email' => 'invalid email address']]
        );
        $this->assertEquals(422, $resp5->getStatusCode());
        $this->assertEquals(120, $resp5->getBody()->getSize());
        $resp5->getBody()->rewind();
        $this->assertEquals(
            '{"success":false,"timestamp":1000,"code":4220,"message":"lang msg",'
                . '"data":[],"errors":{"email":"invalid email address"}}',
            $resp5->getBody()->getContents()
        );
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
                    'limit' => 101,
                    'status' => 'Y',
                    'start_date' => '2025-09-01',
                    'end_date' => '2025-09-30',
                    'permissions' => [5,7],
                    'all' => $bypassPagination ? 1 : 0
                ]]
            ],
        ]);
        $this->setClassCreateObjectMaps(ActionHelper::class, [
            'logger' => $logger,
            'sidebar' => $sidebar,
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
            $bypassPagination ? null : 100,
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
            ['name' => 'DESC', 'status' => 'ASC'],
            $this->getPropertyValue(BaseAction::class, $o, 'sorts')
        );
        $this->assertEquals(
            ['permissions' => [5,7], 'status' => 'Y'],
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
