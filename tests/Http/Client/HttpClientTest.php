<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http\Client;

use InvalidArgumentException;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Http\Client\Exception\HttpClientException;
use Platine\Framework\Http\Client\HttpClient;
use Platine\Framework\Http\Client\HttpResponse;

class HttpClientTest extends PlatineTestCase
{
    public function testConstructEmptyBaseURL(): void
    {
        $o = new HttpClient();
        $this->assertEmpty($o->getBaseUrl());
        $o->setBaseUrl('http://localhost');
        $this->assertEquals($o->getBaseUrl(), 'http://localhost');
    }

    public function testConstructWithBaseURL(): void
    {
        $o = new HttpClient('http://localhost');
        $this->assertEquals($o->getBaseUrl(), 'http://localhost');
    }

    public function testAddCookie(): void
    {
        $o = new HttpClient('http://localhost');
        $o->cookie('cookie_name', 'cookie_value');
        $res = $o->getCookies();

        $this->assertCount(1, $res);
        $this->assertArrayHasKey('cookie_name', $res);
        $this->assertEquals('cookie_value', $res['cookie_name']);
    }

    public function testAddCookies(): void
    {
        $o = new HttpClient('http://localhost');
        $o->cookies([
            'cookie_name1' => 'cookie_value1',
            'cookie_name2' => 'cookie_value2',
        ]);
        $res = $o->getCookies();

        $this->assertCount(2, $res);
        $this->assertArrayHasKey('cookie_name1', $res);
        $this->assertArrayHasKey('cookie_name2', $res);
        $this->assertEquals('cookie_value1', $res['cookie_name1']);
        $this->assertEquals('cookie_value2', $res['cookie_name2']);
    }

    public function testAddCookiesSuperGlobal(): void
    {
        $o = new HttpClient('http://localhost');
        $_COOKIE['cookie_name1'] = 'cookie_value1';
        $_COOKIE['cookie_name2'] = 'cookie_value2';
        $o->cookies();
        $res = $o->getCookies();

        $this->assertCount(2, $res);
        $this->assertArrayHasKey('cookie_name1', $res);
        $this->assertArrayHasKey('cookie_name2', $res);
        $this->assertEquals('cookie_value1', $res['cookie_name1']);
        $this->assertEquals('cookie_value2', $res['cookie_name2']);
    }

    public function testAddParameter(): void
    {
        $o = new HttpClient('http://localhost');
        $o->parameter('param_name', 'param_value');
        $res = $o->getParameters();

        $this->assertCount(1, $res);
        $this->assertArrayHasKey('param_name', $res);
        $this->assertEquals('param_value', $res['param_name']);
    }

    public function testAddParameters(): void
    {
        $o = new HttpClient('http://localhost');
        $o->parameters([
            'param_name1' => 'param_value1',
            'param_name2' => 'param_value2',
        ]);
        $res = $o->getParameters();

        $this->assertCount(2, $res);
        $this->assertArrayHasKey('param_name1', $res);
        $this->assertArrayHasKey('param_name2', $res);
        $this->assertEquals('param_value1', $res['param_name1']);
        $this->assertEquals('param_value2', $res['param_name2']);
    }

    public function testAddHeader(): void
    {
        $o = new HttpClient('http://localhost');
        $o->header('header_name', 'header_value');
        $headers = $o->getHeaders();

        $this->assertCount(1, $headers);
        $this->assertArrayHasKey('header_name', $headers);
        $this->assertCount(1, $headers['header_name']);
        $this->assertEquals('header_value', $headers['header_name'][0]);
    }

    public function testAddHeaders(): void
    {
        $o = new HttpClient('http://localhost');
        $o->headers([
            'header_name1' => 'header_value1',
            'header_name2' => 'header_value2',
        ]);
        $headers = $o->getHeaders();

        $this->assertCount(2, $headers);
        $this->assertArrayHasKey('header_name1', $headers);
        $this->assertCount(1, $headers['header_name1']);
        $this->assertCount(1, $headers['header_name2']);
        $this->assertEquals('header_value1', $headers['header_name1'][0]);
        $this->assertEquals('header_value2', $headers['header_name2'][0]);
    }

    public function testAddContentType(): void
    {
        $o = new HttpClient('http://localhost');
        $o->contentType('application/json');
        $headers = $o->getHeaders();

        $this->assertCount(1, $headers);
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertCount(1, $headers['Content-Type']);
        $this->assertEquals('application/json', $headers['Content-Type'][0]);
    }

    public function testAccept(): void
    {
        $o = new HttpClient('http://localhost');
        $o->accept('application/json');
        $headers = $o->getHeaders();

        $this->assertCount(1, $headers);
        $this->assertArrayHasKey('Accept', $headers);
        $this->assertCount(1, $headers['Accept']);
        $this->assertEquals('application/json', $headers['Accept'][0]);
    }

    public function testAuthorization(): void
    {
        $o = new HttpClient('http://localhost');
        $o->authorization('Bearer', 'MyToken');
        $headers = $o->getHeaders();

        $this->assertCount(1, $headers);
        $this->assertArrayHasKey('Authorization', $headers);
        $this->assertCount(1, $headers['Authorization']);
        $this->assertEquals('Bearer MyToken', $headers['Authorization'][0]);
    }

    public function testAddContentTypeMultipart(): void
    {
        global $mock_uniqid;
        $mock_uniqid = true;

        $o = new HttpClient('http://localhost');
        $o->contentType('multipart/form-data');
        $headers = $o->getHeaders();

        $this->assertCount(1, $headers);
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertCount(1, $headers['Content-Type']);
        $this->assertEquals('multipart/form-data; boundary="uniqid_key"', $headers['Content-Type'][0]);
    }

    public function testBasicAuthentication(): void
    {
        $o = new HttpClient('http://localhost');
        $o->basicAuthentication('user', 'pwd');

        $this->assertEquals('user', $o->getUsername());
        $this->assertEquals('pwd', $o->getPassword());
    }

    public function testTimeout(): void
    {
        $o = new HttpClient('http://localhost');

        // Default
        $this->assertEquals(30, $o->getTimeout());
        $o->timeout(100);

        $this->assertEquals(100, $o->getTimeout());
    }

    public function testVerifySslCertificate(): void
    {
        $o = new HttpClient('http://localhost');

        // Default
        $this->assertTrue($o->isVerifySslCertificate());
        $o->verifySslCertificate(false);

        $this->assertFalse($o->isVerifySslCertificate());
    }

    public function testRequestBaseURLIsEmpty(): void
    {
        $o = new HttpClient('');
        $o->parameter('user', 1);

        $this->expectException(InvalidArgumentException::class);
        $o->get('/foo/bar');
    }

    public function testRequest(): void
    {
        global $mock_curl_exec, $mock_curl_getinfo;
        $mock_curl_exec = true;
        $mock_curl_getinfo = true;

        $o = new HttpClient('http://example.com');
        $o->parameter('user', 1);
        $o->basicAuthentication('user', 'pwd');
        $o->header('lang', 'en');
        $o->cookie('sessionid', '12345678');

        $res = $o->get('/foo/bar');
        $this->assertInstanceOf(HttpResponse::class, $res);
        $this->assertFalse($res->isError());
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals(2, $res->getHeaderSize());
        $this->assertCount(0, $res->getHeaders());
        $this->assertEquals(897, $res->getContentLength());
        $this->assertEquals('http://example.com', $res->getUrl());
        $this->assertEquals('application/json', $res->getContentType());
        $this->assertEquals('curl_content', $res->getContent());
    }

    public function testRequestCurlReturnError(): void
    {
        global $mock_curl_exec, $mock_curl_error;
        $mock_curl_exec = true;
        $mock_curl_error = true;

        $o = new HttpClient('http://example.com');
        $o->parameter('user', 1);
        $o->basicAuthentication('user', 'pwd');

        $this->expectException(HttpClientException::class);
        $o->get('/foo/bar');
    }

    public function testRequestAllHttpMethods(): void
    {
        global $mock_curl_exec, $mock_curl_getinfo;
        $mock_curl_exec = true;
        $mock_curl_getinfo = true;

        $o = new HttpClient('http://example.com');

        $this->assertInstanceOf(HttpResponse::class, $o->get('/foo/bar'));
        $this->assertInstanceOf(HttpResponse::class, $o->post('/foo/bar'));
        $this->assertInstanceOf(HttpResponse::class, $o->put('/foo/bar'));
        $this->assertInstanceOf(HttpResponse::class, $o->delete('/foo/bar'));
        $this->assertInstanceOf(HttpResponse::class, $o->head('/foo/bar'));
        $this->assertInstanceOf(HttpResponse::class, $o->options('/foo/bar'));
        $this->assertInstanceOf(HttpResponse::class, $o->connect('/foo/bar'));
        $this->assertInstanceOf(HttpResponse::class, $o->trace('/foo/bar'));
    }

    public function testRequestUsingHeaderFunction(): void
    {
        global $mock_curl_exec, $mock_curl_setopt_closure, $mock_curl_getinfo;
        $mock_curl_exec = true;
        $mock_curl_setopt_closure = true;
        $mock_curl_getinfo = true;

        $o = new HttpClient('http://example.com');
        $o->parameter('user', 1);
        $o->basicAuthentication('user', 'pwd');
        $o->header('lang', 'en');
        $o->cookie('sessionid', '12345678');

        $res = $o->get('/foo/bar');
        $this->assertInstanceOf(HttpResponse::class, $res);
    }

    public function testRequestWithBodyJson(): void
    {
        global $mock_curl_exec, $mock_curl_getinfo;
        $mock_curl_exec = true;
        $mock_curl_getinfo = true;

        $o = new HttpClient('http://example.com');
        $o->parameter('user', 1);
        $o->basicAuthentication('user', 'pwd');
        $o->header('lang', 'en');
        $o->cookie('sessionid', '12345678');
        $o->json();

        $res = $o->post('/foo/bar', ['foo' => 'bar']);
        $this->assertInstanceOf(HttpResponse::class, $res);
    }

    public function testRequestWithBodyForm(): void
    {
        global $mock_curl_exec, $mock_curl_getinfo;
        $mock_curl_exec = true;
        $mock_curl_getinfo = true;

        $o = new HttpClient('http://example.com');
        $o->parameter('user', 1);
        $o->basicAuthentication('user', 'pwd');
        $o->header('lang', 'en');
        $o->cookie('sessionid', '12345678');
        $o->form();

        $res = $o->post('/foo/bar', ['foo' => 'bar']);
        $this->assertInstanceOf(HttpResponse::class, $res);
    }

    public function testRequestWithBodyMultipart(): void
    {
        global $mock_curl_exec, $mock_curl_getinfo;
        $mock_curl_exec = true;
        $mock_curl_getinfo = true;

        $o = new HttpClient('http://example.com');
        $o->parameter('user', 1);
        $o->basicAuthentication('user', 'pwd');
        $o->header('lang', 'en');
        $o->cookie('sessionid', '12345678');
        $o->mutlipart();

        $res = $o->post('/foo/bar', ['age' => 12, 'foo' => ['data' => 'filedata', 'filename' => 'photo.png']]);
        $this->assertInstanceOf(HttpResponse::class, $res);
    }

    public function testRequestWithBodyMultipartMissingBoundary(): void
    {
        global $mock_curl_exec;
        $mock_curl_exec = true;

        $o = new HttpClient('http://example.com');
        $o->parameter('user', 1);
        $o->basicAuthentication('user', 'pwd');
        $o->header('lang', 'en');
        $o->cookie('sessionid', '12345678');
        $o->header('Content-Type', 'multipart/form-data');

        $this->expectException(InvalidArgumentException::class);
        $o->post('/foo/bar', ['foo' => 'bar']);
    }
}
