<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http\Action;

use Platine\Config\Config;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Auth\Repository\UserRepository;
use Platine\Framework\Helper\ActionHelper;
use Platine\Framework\Http\Action\RestBaseAction;
use Platine\Framework\Http\Response\RestResponse;
use Platine\Framework\Security\SecurityPolicy;
use Platine\Http\ServerRequest;
use Platine\Lang\Lang;
use Platine\Logger\Logger;
use Platine\Orm\Query\EntityQuery;
use Platine\Pagination\Pagination;
use Platine\Test\Framework\Fixture\MyRestBaseAction;

class RestBaseActionTest extends PlatineTestCase
{
    public function testConstruct(): void
    {
        $logger = $this->getMockInstance(Logger::class);
        $this->setClassCreateObjectMaps(ActionHelper::class, [
            'logger' => $logger,
        ]);
        $actionHelper = $this->createObject(ActionHelper::class);

        $o = new MyRestBaseAction($actionHelper);
        $this->assertInstanceOf(MyRestBaseAction::class, $o);
    }

    public function testHandleBypassPagination(): void
    {
        $this->handle(true);
    }

    public function testHandleDoNotBypassPagination(): void
    {
        $this->handle(false);
    }


    public function testHandleRestPaginationDefaultSort(): void
    {
        $this->handleRestPagination(true);
    }

    public function testHandleRestPaginationQueryParamSort(): void
    {
        $this->handleRestPagination(false);
    }

    private function handleRestPagination(bool $defaultSort = false): void
    {
        $query = $this->getMockInstance(EntityQuery::class, [
            'count' => 23,
        ]);
        $repo1 = $this->getMockInstance(UserRepository::class, [
            'query' => $query,
        ]);
        $repo = $this->getMockInstance(UserRepository::class, [
            'filters' => $repo1,
        ]);
        $logger = $this->getMockInstance(Logger::class);
        $pagination = $this->getMockInstance(Pagination::class, [
            'getTotalItems' => 16,
            'getInfo' => ['page' => 1],
        ]);
        $this->setClassCreateObjectMaps(ActionHelper::class, [
            'logger' => $logger,
            'pagination' => $pagination,
        ]);
        $request = $this->getMockInstanceMap(ServerRequest::class, [
            'getQueryParams' => [
                [[
                    'sort' => $defaultSort === false ? 'name:desc,status' : '',
                    'page' => 1,
                    'limit' => 101,
                ]]
            ],
        ]);
        $actionHelper = $this->createObject(ActionHelper::class);

        $o = new MyRestBaseAction($actionHelper);
        /** @var RestResponse $resp */
        $resp = $o->handle($request);

        $this->expectMethodCallCount($repo, 'filters');
        $this->expectMethodCallCount($query, 'count');
        $this->expectMethodCallCount($pagination, 'setTotalItems');
        $this->runPrivateProtectedMethod(
            $o,
            'handleRestPagination',
            [$repo, $query]
        );
        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertEquals('OK', $resp->getReasonPhrase());
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

        $o = new MyRestBaseAction($actionHelper);
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

    public function testRestCreatedResponse(): void
    {
        global $mock_time_to_1000;
        $mock_time_to_1000 = true;

        $logger = $this->getMockInstance(Logger::class);
        $this->setClassCreateObjectMaps(ActionHelper::class, [
            'logger' => $logger,
        ]);
        $actionHelper = $this->createObject(ActionHelper::class);

        $o = new MyRestBaseAction($actionHelper);
        /** @var RestResponse $resp */
        $resp = $this->runPrivateProtectedMethod(
            $o,
            'createdResponse',
            [['foo' => 'bar'], 0]
        );
        $this->assertInstanceOf(RestResponse::class, $resp);
        $this->assertEquals(201, $resp->getStatusCode());
        $this->assertEquals('Created', $resp->getReasonPhrase());
        $this->assertEquals(63, $resp->getBody()->getSize());
        $resp->getBody()->rewind();
        $this->assertEquals(
            '{"success":true,"timestamp":1000,"code":0,"data":{"foo":"bar"}}',
            $resp->getBody()->getContents()
        );
    }

    public function testRestNoContentResponse(): void
    {
        global $mock_time_to_1000;
        $mock_time_to_1000 = true;

        $logger = $this->getMockInstance(Logger::class);
        $this->setClassCreateObjectMaps(ActionHelper::class, [
            'logger' => $logger,
        ]);
        $actionHelper = $this->createObject(ActionHelper::class);

        $o = new MyRestBaseAction($actionHelper);
        /** @var RestResponse $resp */
        $resp = $this->runPrivateProtectedMethod(
            $o,
            'noContentResponse',
            [0]
        );
        $this->assertInstanceOf(RestResponse::class, $resp);
        $this->assertEquals(204, $resp->getStatusCode());
        $this->assertEquals('No Content', $resp->getReasonPhrase());
        $this->assertEquals(52, $resp->getBody()->getSize());
        $resp->getBody()->rewind();
        $this->assertEquals(
            '{"success":true,"timestamp":1000,"code":0,"data":[]}',
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

        $o = new MyRestBaseAction($actionHelper);
        /** @var RestResponse $resp */
        $resp = $this->runPrivateProtectedMethod(
            $o,
            'errorResponse',
            ['Error response']
        );
        $this->assertInstanceOf(RestResponse::class, $resp);
        $this->assertEquals(401, $resp->getStatusCode());
        $this->assertEquals(86, $resp->getBody()->getSize());
        $resp->getBody()->rewind();
        $this->assertEquals(
            '{"success":false,"timestamp":1000,"code":"ERROR","message":"Error response","data":[]}',
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

        $o = new MyRestBaseAction($actionHelper);

        $resp1 = $this->runPrivateProtectedMethod(
            $o,
            'internalServerErrorResponse',
            ['Server Error']
        );
        $this->assertEquals(500, $resp1->getStatusCode());
        $this->assertEquals(100, $resp1->getBody()->getSize());
        $resp1->getBody()->rewind();
        $this->assertEquals(
            '{"success":false,"timestamp":1000,"code":"INTERNAL_SERVER_ERROR","message":"Server Error","data":[]}',
            $resp1->getBody()->getContents()
        );

        //
        $resp2 = $this->runPrivateProtectedMethod(
            $o,
            'badRequestResponse',
            ['Bad Error']
        );
        $this->assertEquals(400, $resp2->getStatusCode());
        $this->assertEquals(87, $resp2->getBody()->getSize());
        $resp2->getBody()->rewind();
        $this->assertEquals(
            '{"success":false,"timestamp":1000,"code":"BAD_REQUEST","message":"Bad Error","data":[]}',
            $resp2->getBody()->getContents()
        );

        //
        $resp3 = $this->runPrivateProtectedMethod(
            $o,
            'conflictResponse',
            ['Conflict Error']
        );
        $this->assertEquals(409, $resp3->getStatusCode());
        $this->assertEquals(99, $resp3->getBody()->getSize());
        $resp3->getBody()->rewind();
        $this->assertEquals(
            '{"success":false,"timestamp":1000,"code":"DUPLICATE_RESOURCE","message":"Conflict Error","data":[]}',
            $resp3->getBody()->getContents()
        );

        //
        $resp4 = $this->runPrivateProtectedMethod(
            $o,
            'notFoundResponse',
            ['Not Found Error']
        );
        $this->assertEquals(404, $resp4->getStatusCode());
        $this->assertEquals(100, $resp4->getBody()->getSize());
        $resp4->getBody()->rewind();
        $this->assertEquals(
            '{"success":false,"timestamp":1000,"code":"RESOURCE_NOT_FOUND","message":"Not Found Error","data":[]}',
            $resp4->getBody()->getContents()
        );

        //
        $resp5 = $this->runPrivateProtectedMethod(
            $o,
            'formValidationResponse',
            [['email' => 'invalid email address']]
        );
        $this->assertEquals(422, $resp5->getStatusCode());
        $this->assertEquals(131, $resp5->getBody()->getSize());
        $resp5->getBody()->rewind();
        $this->assertEquals(
            '{"success":false,"timestamp":1000,"code":"INVALID_INPUT","message":"lang msg","data":[],'
                . '"errors":{"email":"invalid email address"}}',
            $resp5->getBody()->getContents()
        );

        //
        $resp6 = $this->runPrivateProtectedMethod(
            $o,
            'unauthorizedResponse',
            ['User not login']
        );
        $this->assertEquals(401, $resp6->getStatusCode());
        $this->assertEquals(100, $resp6->getBody()->getSize());
        $resp6->getBody()->rewind();
        $this->assertEquals(
            '{"success":false,"timestamp":1000,"code":"UNAUTHORIZED_ACCESS","message":"User not login","data":[]}',
            $resp6->getBody()->getContents()
        );

        $resp7 = $this->runPrivateProtectedMethod(
            $o,
            'forbiddenResponse',
            ['User not login']
        );
        $this->assertEquals(403, $resp7->getStatusCode());
        $this->assertEquals(90, $resp7->getBody()->getSize());
        $resp7->getBody()->rewind();
        $this->assertEquals(
            '{"success":false,"timestamp":1000,"code":"FORBIDDEN","message":"User not login","data":[]}',
            $resp7->getBody()->getContents()
        );
    }

    protected function handle(bool $bypassPagination = false): void
    {
        global $mock_app_httpaction_to_instance;
        $mock_app_httpaction_to_instance = true;

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
            'config' => $config,
            'logger' => $logger,
        ]);
        $actionHelper = $this->createObject(ActionHelper::class);

        $o = new MyRestBaseAction($actionHelper);
        $this->expectMethodCallCount($request, 'getQueryParams');
        $resp = $o->handle($request);
        $this->assertInstanceOf(RestResponse::class, $resp);
        $this->assertEquals(200, $resp->getStatusCode());

        $this->assertEquals(
            $bypassPagination ? null : 101,
            $this->getPropertyValue(RestBaseAction::class, $o, 'limit')
        );
        $this->assertEquals(
            $bypassPagination ? null : 1,
            $this->getPropertyValue(RestBaseAction::class, $o, 'page')
        );

        $this->assertEquals(
            $bypassPagination ? true : false,
            $this->getPropertyValue(RestBaseAction::class, $o, 'all')
        );

        $this->assertEquals(
            ['name' => 'DESC', 'status' => 'ASC'],
            $this->getPropertyValue(RestBaseAction::class, $o, 'sorts')
        );
        $this->assertEquals(
            ['permissions' => [5,7], 'status' => 'Y', 'multi' => [5, 7]],
            $this->getPropertyValue(RestBaseAction::class, $o, 'filters')
        );
    }
}
