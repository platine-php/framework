<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http\Middleware;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Http\Exception\HttpMethodNotAllowedException;
use Platine\Framework\Http\Middleware\RouteMatchMiddleware;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\Response;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequest;
use Platine\Http\Uri;
use Platine\Route\Route;
use Platine\Route\RouteCollection;
use Platine\Route\Router;

/**
 * RouteMatchMiddleware class tests
 *
 * @group core
 * @group route
 */
class RouteMatchMiddlewareTest extends PlatineTestCase
{
    public function testConstructor(): void
    {
        $router = $this->getMockBuilder(Router::class)
                ->getMock();

        $router->expects($this->any())
                ->method('get')
                ->will($this->returnValue('foo'));

        $m = new RouteMatchMiddleware($router, array('GET'));

        $rr = $this->getPrivateProtectedAttribute(RouteMatchMiddleware::class, 'router');
        $amr = $this->getPrivateProtectedAttribute(RouteMatchMiddleware::class, 'allowedMethods');

        $this->assertEquals($router, $rr->getValue($m));
        $this->assertContains('GET', $amr->getValue($m));
    }

    public function testProcessRouteIsMatch(): void
    {
        $routeCollection = new RouteCollection();
        $routeCollection->add(new Route('/pattern', 'handler', 'name', array('GET')));

        $router = new Router($routeCollection);

        $responseObject = new Response();
        $responseObject->getBody()->write('RouteIsMatch');

        $requestHandler = $this->getMockBuilder(RequestHandlerInterface::class)
                ->getMock();

        $requestHandler->expects($this->any())
                ->method('handle')
                ->will($this->returnValue($responseObject));

        $uri = $this->getMockBuilder(Uri::class)
                ->getMock();

        $uri->expects($this->any())
                ->method('getPath')
                ->will($this->returnValue('/pattern'));

        $request = $this->getMockBuilder(ServerRequest::class)
                ->getMock();

        $request->expects($this->any())
                ->method('getUri')
                ->will($this->returnValue($uri));

        $request->expects($this->any())
                ->method('getMethod')
                ->will($this->returnValue('GET'));

        $m = new RouteMatchMiddleware($router, array('GET'));

        $response = $m->process($request, $requestHandler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('RouteIsMatch', $response->getBody());
    }

    public function testProcessRouteIsMatchUsingParameters(): void
    {
        $routeCollection = new RouteCollection();
        $routeCollection->add(new Route('/pattern/{id}', 'handler', 'name', array('GET')));

        $router = new Router($routeCollection);

        $responseObject = new Response();
        $responseObject->getBody()->write('RouteIsMatchWithParameters');

        $requestHandler = $this->getMockBuilder(RequestHandlerInterface::class)
                ->getMock();

        $requestHandler->expects($this->any())
                ->method('handle')
                ->will($this->returnValue($responseObject));

        $uri = $this->getMockBuilder(Uri::class)
                ->getMock();

        $uri->expects($this->any())
                ->method('getPath')
                ->will($this->returnValue('/pattern/12'));

        $request = $this->getMockBuilder(ServerRequest::class)
                ->getMock();

        $request->expects($this->any())
                ->method('getUri')
                ->will($this->returnValue($uri));

        $request->expects($this->any())
                ->method('getMethod')
                ->will($this->returnValue('GET'));

        $m = new RouteMatchMiddleware($router, array('GET'));

        $response = $m->process($request, $requestHandler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('RouteIsMatchWithParameters', $response->getBody());
    }

    public function testProcessMethodNotAllowed(): void
    {
        $routeCollection = new RouteCollection();
        $routeCollection->add(new Route('/pattern', 'handler', 'name', array('GET')));

        $router = new Router($routeCollection);

        $requestHandler = $this->getMockBuilder(RequestHandlerInterface::class)
                ->getMock();

        $uri = $this->getMockBuilder(Uri::class)
                ->getMock();

        $uri->expects($this->any())
                ->method('getPath')
                ->will($this->returnValue('/pattern'));

        $request = $this->getMockBuilder(ServerRequest::class)
                ->getMock();

        $request->expects($this->any())
                ->method('getUri')
                ->will($this->returnValue($uri));

        $request->expects($this->any())
                ->method('getMethod')
                ->will($this->returnValue('POST'));

        $m = new RouteMatchMiddleware($router, array('GET'));

        $this->expectException(HttpMethodNotAllowedException::class);
        $response = $m->process($request, $requestHandler);
    }

    public function testProcessRouteNotMatch(): void
    {
        $routeCollection = new RouteCollection();
        $routeCollection->add(new Route('/pattern', 'handler', 'name', array('GET')));

        $router = new Router($routeCollection);

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

        $m = new RouteMatchMiddleware($router, array('GET'));

        $response = $m->process($request, $requestHandler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('RouteNotMatch', $response->getBody());
    }
}
