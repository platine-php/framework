<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http\Middleware;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Http\Middleware\BodyParserMiddleware;
use Platine\Framework\Kernel\HttpKernel;
use Platine\Http\ServerRequest;
use Platine\Http\Stream;
use RuntimeException;

/*
 * @group core
 * @group framework
 */
class BodyParserMiddlewareTest extends PlatineTestCase
{

    public function testProcess(): void
    {
        $request = $this->getMockInstance(ServerRequest::class, [
            'getParsedBody' => null
        ]);
        $request->expects($this->exactly(1))
                ->method('withParsedBody');

        $handler = $this->getMockInstance(HttpKernel::class);

        $o = new BodyParserMiddleware();
        $res = $o->process($request, $handler);

        $this->assertEquals(0, $res->getStatusCode());
    }

    public function testParsers(): void
    {
        $o = new BodyParserMiddleware();
        $o->registerParser('my-content-type', [$this, 'myParser']);

        $this->assertIsCallable($o->getParser('my-content-type'));

        $this->expectException(RuntimeException::class);
        $o->getParser('not-found-content-type');
    }

    public function testProcessCustomeParser(): void
    {
        $request = $this->getMockInstance(ServerRequest::class, [
            'getParsedBody' => null,
            'getHeader' => ['my-content-type'],
        ]);
        $request->expects($this->exactly(1))
                ->method('withParsedBody')
                ->with(['foo' => 'bar']);

        $handler = $this->getMockInstance(HttpKernel::class);

        $o = new BodyParserMiddleware();
        $o->registerParser('my-content-type', [$this, 'myParser']);
        $res = $o->process($request, $handler);

        $this->assertEquals(0, $res->getStatusCode());
    }

    public function testProcessInvalidParserReturnType(): void
    {
        $request = $this->getMockInstance(ServerRequest::class, [
            'getParsedBody' => null,
            'getHeader' => ['my-content-type'],
        ]);

        $handler = $this->getMockInstance(HttpKernel::class);

        $o = new BodyParserMiddleware();
        $o->registerParser('my-content-type', [$this, 'invalidParserReturnType']);
        $this->expectException(RuntimeException::class);
        $o->process($request, $handler);
    }

    public function testProcessJson(): void
    {
        $body = new Stream('{"foo": "bar"}');
        $request = $this->getMockInstance(ServerRequest::class, [
            'getParsedBody' => null,
            'getBody' => $body,
            'getHeader' => ['application/json'],
        ]);

        $request->expects($this->exactly(1))
                ->method('withParsedBody')
                ->with(['foo' => 'bar']);

        $handler = $this->getMockInstance(HttpKernel::class);

        $o = new BodyParserMiddleware();
        $res = $o->process($request, $handler);

        $this->assertEquals(0, $res->getStatusCode());
    }

    public function testProcessForm(): void
    {
        $body = new Stream('foo=bar');
        $request = $this->getMockInstance(ServerRequest::class, [
            'getParsedBody' => null,
            'getBody' => $body,
            'getHeader' => ['application/x-www-form-urlencoded'],
        ]);

        $request->expects($this->exactly(1))
                ->method('withParsedBody')
                ->with(['foo' => 'bar']);

        $handler = $this->getMockInstance(HttpKernel::class);

        $o = new BodyParserMiddleware();
        $res = $o->process($request, $handler);

        $this->assertEquals(0, $res->getStatusCode());
    }

    public function testProcessJsonRFC6839(): void
    {
        $body = new Stream('{"foo": "bar"}');
        $request = $this->getMockInstance(ServerRequest::class, [
            'getParsedBody' => null,
            'getBody' => $body,
            'getHeader' => ['application/app+json'],
        ]);

        $request->expects($this->exactly(1))
                ->method('withParsedBody')
                ->with(['foo' => 'bar']);

        $handler = $this->getMockInstance(HttpKernel::class);

        $o = new BodyParserMiddleware();
        $res = $o->process($request, $handler);

        $this->assertEquals(0, $res->getStatusCode());
    }

    public function testProcessNoParser(): void
    {
        $body = new Stream('{"foo": "bar"}');
        $request = $this->getMockInstance(ServerRequest::class, [
            'getParsedBody' => null,
            'getBody' => $body,
            'getHeader' => ['foo-content-type'],
        ]);

        $request->expects($this->exactly(1))
                ->method('withParsedBody')
                ->with(null);

        $handler = $this->getMockInstance(HttpKernel::class);

        $o = new BodyParserMiddleware();
        $res = $o->process($request, $handler);

        $this->assertEquals(0, $res->getStatusCode());
    }

    public function myParser($body)
    {
        return ['foo' => 'bar'];
    }

    public function invalidParserReturnType($body)
    {
        return 34;
    }
}
