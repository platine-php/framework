<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Http\RequestData;
use Platine\Http\ServerRequest;
use Platine\Http\UploadedFile;

/*
 * @group core
 * @group framework
 */
class RequestDataTest extends PlatineTestCase
{
    public function testPosts(): void
    {
        $request = $this->getMockInstance(ServerRequest::class, [
            'getParsedBody' => [
                'foo' => 'bar'
            ]
        ]);
        $o = new RequestData($request);
        $this->assertCount(1, $o->posts());
        $this->assertNull($o->post('bar'));
        $this->assertEquals('bar', $o->post('foo'));
    }

    public function testFiles(): void
    {
        $file = $this->getMockInstance(UploadedFile::class);
        $request = $this->getMockInstance(ServerRequest::class, [
            'getUploadedFiles' => [
                'foo' => $file
            ]
        ]);
        $o = new RequestData($request);
        $this->assertCount(1, $o->files());
        $this->assertInstanceOf(UploadedFile::class, $o->file('foo'));
        $this->assertEquals($file, $o->file('foo'));
    }

    public function testGets(): void
    {
        $request = $this->getMockInstance(ServerRequest::class, [
            'getQueryParams' => [
                'foo' => 'bar'
            ]
        ]);
        $o = new RequestData($request);
        $this->assertCount(1, $o->gets());
        $this->assertNull($o->get('bar'));
        $this->assertEquals('bar', $o->get('foo'));
    }

    public function testServers(): void
    {
        $request = $this->getMockInstance(ServerRequest::class, [
            'getServerParams' => [
                'foo' => 'bar'
            ]
        ]);
        $o = new RequestData($request);
        $this->assertCount(1, $o->servers());
        $this->assertNull($o->server('bar'));
        $this->assertEquals('bar', $o->server('foo'));
    }

    public function testCookies(): void
    {
        $request = $this->getMockInstance(ServerRequest::class, [
            'getCookieParams' => [
                'foo' => 'bar'
            ]
        ]);
        $o = new RequestData($request);
        $this->assertCount(1, $o->cookies());
        $this->assertNull($o->cookie('bar'));
        $this->assertEquals('bar', $o->cookie('foo'));
    }

    public function testFilterXss(): void
    {
        $request = $this->getMockInstance(ServerRequest::class, [
            'getQueryParams' => [
                'foo' => 'bar',
                'xss_ok' => '<b>bar</b>',
                'xss_nok' => '<a href="foo">bar</a>',
            ]
        ]);
        $o = new RequestData($request);
        $this->assertNull($o->get('bar'));
        $this->assertEquals('bar', $o->get('foo'));
        $this->assertEquals('<b>bar</b>', $o->get('xss_ok'));
        $this->assertEquals('<a>bar</a>', $o->get('xss_nok'));
        $this->assertEquals('<a href="foo">bar</a>', $o->get('xss_nok', null, false)); // disable XSS
    }
}
