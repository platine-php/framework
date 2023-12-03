<?php

declare(strict_types=1);

namespace Platine\Test\Framework\OAuth2\Handler;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\OAuth2\Handler\AuthorizationRequestHandler;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequest;
use Platine\OAuth2\AuthorizationServer;

/*
 * @group core
 * @group framework
 */
class AuthorizationRequestHandlerTest extends PlatineTestCase
{
    public function testConstruct(): void
    {
        $authorizationServer = $this->getMockInstance(AuthorizationServer::class);
        $o = new AuthorizationRequestHandler(
            $authorizationServer
        );
        $this->assertInstanceOf(AuthorizationRequestHandler::class, $o);
    }

    public function testProcess()
    {
        $request = $this->getMockInstance(ServerRequest::class);
        $authorizationServer = $this->getMockInstance(AuthorizationServer::class);

        $authorizationServer->expects($this->once())
                ->method('handleAuthorizationRequest')
                ->with($request);

        $o = new AuthorizationRequestHandler(
            $authorizationServer
        );

        $res = $o->handle($request);
        $this->assertInstanceOf(ResponseInterface::class, $res);
    }
}
