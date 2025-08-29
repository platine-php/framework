<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http\Action;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Helper\ActionHelper;
use Platine\Framework\Http\Exception\HttpNotFoundException;
use Platine\Framework\Http\Response\JsonResponse;
use Platine\Http\ServerRequest;
use Platine\Logger\Logger;
use Platine\Route\Route;
use Platine\Test\Framework\Fixture\MyBaseResourceAction;

class BaseResourceActionTest extends PlatineTestCase
{
    public function testResponseNotFound(): void
    {
        $route = new Route('/user/create', 'foo@method', 'user_create');
        $logger = $this->getMockInstance(Logger::class);
        $request = $this->getMockInstance(ServerRequest::class, [
            'getAttribute' => $route,
        ]);
        $this->setClassCreateObjectMaps(ActionHelper::class, [
            'logger' => $logger,
        ]);
        $actionHelper = $this->createObject(ActionHelper::class);

        $o = new MyBaseResourceAction($actionHelper);
        $this->assertInstanceOf(MyBaseResourceAction::class, $o);
        $this->expectException(HttpNotFoundException::class);
        $o->handle($request);
    }

    public function testResponseSucess(): void
    {
        $route = new Route('/user/create', 'foo@create', 'user_create');
        $logger = $this->getMockInstance(Logger::class);
        $request = $this->getMockInstance(ServerRequest::class, [
            'getAttribute' => $route,
        ]);
        $this->setClassCreateObjectMaps(ActionHelper::class, [
            'logger' => $logger,
        ]);
        $actionHelper = $this->createObject(ActionHelper::class);

        $o = new MyBaseResourceAction($actionHelper);
        $this->assertInstanceOf(MyBaseResourceAction::class, $o);
        $resp = $o->handle($request);
        $this->assertInstanceOf(JsonResponse::class, $resp);
        $this->assertEquals(200, $resp->getStatusCode());
    }
}
