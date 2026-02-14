<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http\Action;

use Platine\Config\Config;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Config\AppDatabaseConfig;
use Platine\Framework\Config\DatabaseConfigLoader;
use Platine\Framework\Helper\ActionHelper;
use Platine\Framework\Helper\ViewContext;
use Platine\Framework\Http\Action\BaseAction;
use Platine\Framework\Http\Response\RedirectResponse;
use Platine\Framework\Http\Response\TemplateResponse;
use Platine\Http\ServerRequest;
use Platine\Logger\Logger;
use Platine\Test\Framework\Fixture\MyBaseConfigurationAction;
use Platine\Test\Framework\Fixture\MyEntity;

class BaseConfigurationActionTest extends PlatineTestCase
{
    public function testConstruct(): void
    {
        $logger = $this->getMockInstance(Logger::class);
        $dbConfig = $this->getMockInstance(AppDatabaseConfig::class);
        $this->setClassCreateObjectMaps(ActionHelper::class, [
            'logger' => $logger,
        ]);
        $actionHelper = $this->createObject(ActionHelper::class);

        $o = new MyBaseConfigurationAction($actionHelper, $dbConfig);
        $this->assertInstanceOf(MyBaseConfigurationAction::class, $o);

        $this->assertEmpty($this->runPrivateProtectedMethod(
            $o,
            'getViewName',
            []
        ));

        $this->assertEmpty($this->runPrivateProtectedMethod(
            $o,
            'getRouteName',
            []
        ));
    }



    public function testRespondRequestMethodGet(): void
    {
        global $mock_app_httpaction_to_instance,
               $mock_app_form_to_instance,
               $mock_app_form_server_request_methods;
        $mock_app_httpaction_to_instance = true;
        $mock_app_form_to_instance = true;
        $mock_app_form_server_request_methods = [];

        $logger = $this->getMockInstance(Logger::class);
        $dbConfig = $this->getMockInstance(AppDatabaseConfig::class, [
            'get' => 'foo',
        ]);
        $request = $this->getMockInstanceMap(ServerRequest::class, [
            'getMethod' => [
                ['GET'],
            ],
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['pagination.max_per_page', 100, 100],
                ['pagination.max_limit', 1000, 1000],
            ],
        ]);

        $this->setClassCreateObjectMaps(ActionHelper::class, [
            'config' => $config,
            'logger' => $logger,
        ]);
        $actionHelper = $this->createObject(ActionHelper::class);

        $o = new MyBaseConfigurationAction($actionHelper, $dbConfig);
        $this->expectMethodCallCount($request, 'getMethod', 2);
        $this->expectMethodCallCount($dbConfig, 'get', 1);
        $resp = $o->handle($request);
        $this->assertInstanceOf(TemplateResponse::class, $resp);
        $this->assertEquals(200, $resp->getStatusCode());

        $this->assertEquals('', $this->getPropertyValue(BaseAction::class, $o, 'viewName'));
        $this->assertEquals(1, $this->getPropertyValue(BaseAction::class, $o, 'page'));
    }

    public function testRespondSaveFormValidationFailed(): void
    {
        global $mock_app_httpaction_to_instance,
               $mock_app_form_to_instance,
               $mock_app_form_server_request_methods;
        $mock_app_httpaction_to_instance = true;
        $mock_app_form_to_instance = true;
        $mock_app_form_server_request_methods = [];

        $logger = $this->getMockInstance(Logger::class);
        $viewContext = $this->getMockInstance(ViewContext::class);
        $dbConfig = $this->getMockInstance(AppDatabaseConfig::class, [
            'get' => 'foo',
        ]);
        $request = $this->getMockInstanceMap(ServerRequest::class, [
            'getMethod' => [
                ['POST'],
            ],
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['pagination.max_per_page', 100, 100],
                ['pagination.max_limit', 1000, 1000],
            ],
        ]);
        $this->setClassCreateObjectMaps(ActionHelper::class, [
            'config' => $config,
            'logger' => $logger,
            'context' => $viewContext,
        ]);
        $actionHelper = $this->createObject(ActionHelper::class);

        $o = new MyBaseConfigurationAction($actionHelper, $dbConfig);
        $this->expectMethodCallCount($request, 'getMethod', 2);
        $this->expectMethodCallCount($dbConfig, 'get', 0);
        $this->expectMethodCallCount($viewContext, 'set', 9);
        $resp = $o->handle($request);
        $this->assertInstanceOf(TemplateResponse::class, $resp);
        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertEquals('', $this->getPropertyValue(BaseAction::class, $o, 'viewName'));
        $this->assertEquals(1, $this->getPropertyValue(BaseAction::class, $o, 'page'));
    }

    public function testRespondSaveSuccess(): void
    {
        global $mock_app_httpaction_to_instance,
               $mock_app_form_to_instance,
               $mock_app_form_server_request_methods;
        $mock_app_httpaction_to_instance = true;
        $mock_app_form_to_instance = true;
        $mock_app_form_server_request_methods = [];

        $entity = $this->getMockInstance(MyEntity::class);
        $logger = $this->getMockInstance(Logger::class);
        $dbLoader = $this->getMockInstance(DatabaseConfigLoader::class, [
            'loadConfig' => $entity,
        ]);
        $viewContext = $this->getMockInstance(ViewContext::class);
        $dbConfig = $this->getMockInstanceMap(AppDatabaseConfig::class, [
            'getLoader' => [
                [$dbLoader]
            ],
            'has' => [
                ['app.name', true],
                ['app.array', false],
                ['app.callable', false],
            ],
        ]);
        $request = $this->getMockInstanceMap(ServerRequest::class, [
            'getMethod' => [
                ['POST'],
            ],
            'getParsedBody' => [
                [['name' => 'foo', 'status' => 'bar']],
            ],
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['pagination.max_per_page', 100, 100],
                ['pagination.max_limit', 1000, 1000],
            ],
        ]);
        $this->setClassCreateObjectMaps(ActionHelper::class, [
            'config' => $config,
            'logger' => $logger,
            'context' => $viewContext,
        ]);
        $actionHelper = $this->createObject(ActionHelper::class);

        $o = new MyBaseConfigurationAction($actionHelper, $dbConfig);
        $this->expectMethodCallCount($request, 'getMethod', 1);
        $this->expectMethodCallCount($dbConfig, 'get', 1);
        $this->expectMethodCallCount($viewContext, 'set', 1);
        $this->expectMethodCallCount($dbLoader, 'updateConfig', 1);
        $this->expectMethodCallCount($dbLoader, 'insertConfig', 2);

        $resp = $o->handle($request);
        $this->assertInstanceOf(RedirectResponse::class, $resp);
        $this->assertEquals(302, $resp->getStatusCode());
        $this->assertEquals('', $this->getPropertyValue(BaseAction::class, $o, 'viewName'));
        $this->assertEquals(1, $this->getPropertyValue(BaseAction::class, $o, 'page'));
    }
}
