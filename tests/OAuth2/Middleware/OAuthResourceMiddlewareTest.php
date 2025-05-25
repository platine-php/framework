<?php

declare(strict_types=1);

namespace Platine\Test\Framework\OAuth2\Middleware;

use Platine\Config\Config;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\OAuth2\Middleware\OAuthResourceMiddleware;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\Response;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequest;
use Platine\OAuth2\Entity\AccessToken;
use Platine\OAuth2\Exception\InvalidAccessTokenException;
use Platine\OAuth2\ResourceServer;
use Platine\Route\Route;

/*
 * @group core
 * @group framework
 */
class OAuthResourceMiddlewareTest extends PlatineTestCase
{
    public function testConstruct(): void
    {
        $cfg = $this->getMockInstance(Config::class);
        $resourceServer = $this->getMockInstance(ResourceServer::class);
        $o = new OAuthResourceMiddleware($resourceServer, $cfg);
        $this->assertInstanceOf(OAuthResourceMiddleware::class, $o);
    }

    public function testProcessRouteNotFound()
    {
        $response = $this->getMockInstance(Response::class);

        $request = $this->getMockInstance(
            ServerRequest::class,
            [
                'getAttribute' => null
            ],
            [
                'withAttribute',
            ]
        );
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $handler->expects($this->once())
                ->method('handle')
                ->with($request)
                ->will($this->returnValue($response));

        $resourceServer = $this->getMockInstance(ResourceServer::class, [
            'getAccessToken' => null
        ]);

        $cfg = $this->getMockInstance(Config::class);
        $o = new OauthResourceMiddleware($resourceServer, $cfg);

        $res = $o->process($request, $handler);
        $this->assertInstanceOf(ResponseInterface::class, $res);
    }

    public function testProcessUrlWhiteList()
    {
        $route = $this->getMockInstance(Route::class, [
            'getName' => 'my_route'
        ]);
        $response = $this->getMockInstance(Response::class);

        $request = $this->getMockInstance(
            ServerRequest::class,
            [
                'getAttribute' => $route
            ],
            [
                'withAttribute',
            ]
        );
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $handler->expects($this->once())
                ->method('handle')
                ->with($request)
                ->will($this->returnValue($response));

        $resourceServer = $this->getMockInstance(ResourceServer::class, [
            'getAccessToken' => null
        ]);

        $cfg = $this->getMockInstance(Config::class, [
            'get' => ['my_route']
        ]);
        $o = new OauthResourceMiddleware($resourceServer, $cfg);

        $res = $o->process($request, $handler);
        $this->assertInstanceOf(ResponseInterface::class, $res);
    }

    public function testProcessTokenNotFound()
    {
        $route = $this->getMockInstance(Route::class, [
            'getName' => 'my_route'
        ]);
        $response = $this->getMockInstance(Response::class);

        $request = $this->getMockInstance(
            ServerRequest::class,
            [
                'getAttribute' => $route
            ],
            [
                'withAttribute',
            ]
        );
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $handler->expects($this->any())
                ->method('handle')
                ->will($this->returnValue($response));

        $resourceServer = $this->getMockInstance(ResourceServer::class, [
            'getAccessToken' => null
        ]);

        $resourceServer->expects($this->any())
                ->method('getAccessToken')
                ->willThrowException(new InvalidAccessTokenException('', ''));

        $cfg = $this->getMockInstance(Config::class, [
            'get' => []
        ]);
        $o = new OauthResourceMiddleware($resourceServer, $cfg);

        $res = $o->process($request, $handler);
        $this->assertInstanceOf(ResponseInterface::class, $res);
        $this->assertEquals(401, $res->getStatusCode());
    }

    public function testProcessTokenIsNull()
    {
        $route = $this->getMockInstance(Route::class, [
            'getName' => 'my_route'
        ]);
        $response = $this->getMockInstance(Response::class);

        $request = $this->getMockInstance(
            ServerRequest::class,
            [
                'getAttribute' => $route
            ],
            [
                'withAttribute',
            ]
        );
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $handler->expects($this->any())
                ->method('handle')
                ->will($this->returnValue($response));

        $resourceServer = $this->getMockInstance(ResourceServer::class, [
            'getAccessToken' => null
        ]);

        $cfg = $this->getMockInstance(Config::class, [
            'get' => []
        ]);
        $o = new OauthResourceMiddleware($resourceServer, $cfg);

        $res = $o->process($request, $handler);
        $this->assertInstanceOf(ResponseInterface::class, $res);
        $this->assertEquals(401, $res->getStatusCode());
    }

    public function testProcessSuccess()
    {
        $route = $this->getMockInstance(Route::class, [
            'getName' => 'my_route'
        ]);
        $response = $this->getMockInstance(Response::class, [
            'getStatusCode' => 200
        ]);

        $request = $this->getMockInstance(
            ServerRequest::class,
            [
                'getAttribute' => $route
            ],
            [
                'withAttribute',
            ]
        );
        $token = $this->getMockInstance(AccessToken::class);


        $handler = $this->getMockBuilder(RequestHandlerInterface::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $handler->expects($this->any())
                ->method('handle')
                ->will($this->returnValue($response));

        $resourceServer = $this->getMockInstance(ResourceServer::class, [
            'getAccessToken' => $token
        ]);

        $cfg = $this->getMockInstance(Config::class, [
            'get' => []
        ]);
        $o = new OauthResourceMiddleware($resourceServer, $cfg);

        $res = $o->process($request, $handler);
        $this->assertInstanceOf(ResponseInterface::class, $res);
        $this->assertEquals(200, $res->getStatusCode());
    }
}
