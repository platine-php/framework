<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http\Middleware;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Http\Middleware\SecurityPolicyMiddleware;
use Platine\Framework\Kernel\HttpKernel;
use Platine\Framework\Security\SecurityPolicy;
use Platine\Http\ServerRequest;
use Platine\Route\Route;
use Platine\Test\Framework\Fixture\MyResponse;

/*
 * @group core
 * @group framework
 */
class SecurityPolicyMiddlewareTest extends PlatineTestCase
{
    public function testProcessRouteNotMatch(): void
    {
        $securityPolicy = $this->getMockInstance(SecurityPolicy::class);
        $request = $this->getMockInstance(ServerRequest::class);
        $handler = $this->getMockInstance(HttpKernel::class);

        $o = new SecurityPolicyMiddleware($securityPolicy);
        $res = $o->process($request, $handler);

        $this->assertEquals(0, $res->getStatusCode());
    }

    public function testProcessSuccess(): void
    {
        $route = $this->getMockInstance(Route::class, [
            'getPattern' => '/bar/foo'
        ]);
        $request = $this->getMockInstance(ServerRequest::class, [
            'getAttribute' => $route,
            'getMethod' => 'POST',
            'getParsedBody' => ['csrf_key' => 'foo'],
        ]);
        $response = new MyResponse();
        $handler = $this->getMockInstance(HttpKernel::class, [
            'handle' => $response
        ]);

        $securityPolicy = $this->getMockInstance(SecurityPolicy::class, [
            'headers' => ['Foo' => 'Bar']
        ]);


        $o = new SecurityPolicyMiddleware($securityPolicy);
        $res = $o->process($request, $handler);

        $this->assertEquals(300, $res->getStatusCode());
    }
}
