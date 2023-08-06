<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http\Client;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Http\Client\HttpCookie;
use Platine\Framework\Http\Client\HttpResponse;

class HttpResponseTest extends PlatineTestCase
{
    public function testConstruct(): void
    {
        $requestInfo = [
            'url' => 'http://example.com',
            'content_type' => 'application/json',
            'http_code' => 200,
            'header_size' => 2,
            'content_length' => 897,
        ];

        $headers = [
            'Content-Type' => 'application/json',
            'Set-Cookie' => ['language=en; '
            . 'Expires=Wed, 01-Jul-2020 00:00:00 GMT; '
            . 'Max-Age=10; Domain=example.com; Path=/secure; Secure; '
            . 'HttpOnly; SameSite=Lax'],
        ];
        $response = '  <p>Hello World</p>';
        $error = '';
        $o = new HttpResponse($requestInfo, $headers, $response, $error);
        $this->assertInstanceOf(HttpResponse::class, $o);
        $this->assertInstanceOf(HttpCookie::class, $o->getCookie('language'));
        $this->assertFalse($o->isError());
        $this->assertEquals('en', $o->getCookie('language')->getValue());
        $this->assertEquals(200, $o->getStatusCode());
        $this->assertEquals(2, $o->getHeaderSize());
        $this->assertCount(2, $o->getHeaders());
        $this->assertCount(1, $o->getCookies());
        $this->assertEquals(897, $o->getContentLength());
        $this->assertEquals('http://example.com', $o->getUrl());
        $this->assertEquals('application/json', $o->getContentType());
        $this->assertEquals('<p>Hello World</p>', $o->getContent());
        $this->assertEquals('application/json', $o->getHeader('Content-Type'));
    }

    public function testJson(): void
    {
        $requestInfo = [
            'url' => 'http://example.com',
            'content_type' => 'application/json',
            'http_code' => 200,
            'header_size' => 2,
            'content_length' => 897,
        ];

        $headers = [
            'Content-Type' => 'application/json',
        ];
        $response = '  {"foo":"bar"}';
        $error = '';
        $o = new HttpResponse($requestInfo, $headers, $response, $error);
        $this->assertInstanceOf(HttpResponse::class, $o);
        $this->assertEquals('application/json', $o->getHeader('Content-Type'));
        $this->assertIsObject($o->json());
        $this->assertEquals('bar', $o->json()->foo);
    }

    public function testXml(): void
    {
        $requestInfo = [
            'url' => 'http://example.com',
            'content_type' => 'application/xml',
            'http_code' => 200,
            'header_size' => 2,
            'content_length' => 897,
        ];

        $headers = [
            'Content-Type' => 'application/xml',
        ];
        $response = '  <xml><foo>bar</foo></xml>';
        $error = '';
        $o = new HttpResponse($requestInfo, $headers, $response, $error);
        $this->assertInstanceOf(HttpResponse::class, $o);
        $this->assertEquals('application/xml', $o->getHeader('Content-Type'));
        $this->assertIsArray($o->xml());
        $this->assertArrayHasKey('foo', $o->xml());
        $this->assertEquals('bar', $o->xml()['foo']);
    }
}
