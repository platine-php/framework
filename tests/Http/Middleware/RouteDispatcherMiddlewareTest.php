<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http\Middleware;

use Platine\Container\Container;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Http\Middleware\RouteDispatcherMiddleware;
use Platine\Http\Handler\MiddlewareResolver;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\Response;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequest;
use Platine\Http\Uri;
use Platine\Route\Route;

/**
 * RouteDispatcherMiddleware class tests
 *
 * @group core
 * @group route
 */
class RouteDispatcherMiddlewareTest extends PlatineTestCase
{

    public function testConstructor(): void
    {
        $container = $this->getMockBuilder(Container::class)
                ->getMock();

        $resolver = new MiddlewareResolver($container);

        $m = new RouteDispatcherMiddleware($resolver);

        $rr = $this->getPrivateProtectedAttribute(RouteDispatcherMiddleware::class, 'resolver');

        $this->assertEquals($resolver, $rr->getValue($m));
    }

    public function testProcessRouteAttributeNotFound(): void
    {
        $responseObject = new Response();
        $responseObject->getBody()->write('RouteNotMatch');

        $requestHandler = $this->getMockBuilder(RequestHandlerInterface::class)
                ->getMock();

        $requestHandler->expects($this->any())
                ->method('handle')
                ->will($this->returnValue($responseObject));

        $uri = $this->getMockBuilder(Uri::class)
                ->getMock();

        $uri->expects($this->any())
                ->method('getPath')
                ->will($this->returnValue('/foopattern'));

        $request = $this->getMockBuilder(ServerRequest::class)
                ->getMock();

        $request->expects($this->any())
                ->method('getUri')
                ->will($this->returnValue($uri));

        $request->expects($this->any())
                ->method('getMethod')
                ->will($this->returnValue('GET'));

        $container = $this->getMockBuilder(Container::class)
                ->getMock();

        $resolver = new MiddlewareResolver($container);

        $m = new RouteDispatcherMiddleware($resolver);

        $response = $m->process($request, $requestHandler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('RouteNotMatch', $response->getBody());
    }

    public function testProcessRouteAttributeFound(): void
    {
        $requestHandler = $this->getMockBuilder(RequestHandlerInterface::class)
                ->getMock();

        $uri = $this->getMockBuilder(Uri::class)
                ->getMock();

        $uri->expects($this->any())
                ->method('getPath')
                ->will($this->returnValue('/foopattern'));

        $request = $this->getMockBuilder(ServerRequest::class)
                ->getMock();

        $request->expects($this->any())
                ->method('getUri')
                ->will($this->returnValue($uri));

        $request->expects($this->any())
                ->method('getMethod')
                ->will($this->returnValue('GET'));

        $route = new Route('/pattern', function () {
                    $responseObject = new Response();
                    $responseObject->getBody()->write('RouteNotMatch');
                    return $responseObject;
        }, 'name', array('GET'));

        $request->expects($this->any())
                ->method('getAttribute')
                ->will($this->returnValue($route));

        $container = $this->getMockBuilder(Container::class)
                ->getMock();

        $resolver = new MiddlewareResolver($container);

        $m = new RouteDispatcherMiddleware($resolver);

        $response = $m->process($request, $requestHandler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('RouteNotMatch', $response->getBody());
    }
}
