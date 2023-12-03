<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http\Exception;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Http\Exception\HttpException;
use Platine\Http\ServerRequest;
use Platine\Http\ServerRequestInterface;

class HttpExceptionTest extends PlatineTestCase
{
    public function testConstructor(): void
    {
        $request = $this->getMockInstance(ServerRequest::class);
        $o = new HttpException($request);
        $this->assertInstanceOf(ServerRequestInterface::class, $o->getRequest());
    }

    public function testTitleAndDescription(): void
    {
        $request = $this->getMockInstance(ServerRequest::class);
        $o = new HttpException($request);
        $o->setDescription('exception description');
        $o->setTitle('exception title');

        $this->assertEquals('exception description', $o->getDescription());
        $this->assertEquals('exception title', $o->getTitle());
    }

    public function testHeaders(): void
    {
        $request = $this->getMockInstance(ServerRequest::class);
        $o = new HttpException($request);
        $o->setHeaders(['foo' => 'bar']);

        $headers = $o->getHeaders();

        $this->assertCount(1, $headers);
        $this->assertArrayHasKey('foo', $headers);
        $this->assertEquals('bar', $headers['foo']);
    }
}
