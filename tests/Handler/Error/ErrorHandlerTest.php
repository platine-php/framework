<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Handler\Error;

use Platine\Framework\Handler\Error\ErrorHandler;
use Platine\Framework\Handler\Error\Renderer\TextPlainErrorRenderer;
use Platine\Http\ServerRequest;
use Platine\Logger\Logger;

class ErrorHandlerTest extends BaseErrorHandlerTestCase
{
    public function testHandle(): void
    {
        $request = $this->getMockInstance(ServerRequest::class);
        $logger = $this->getMockInstance(Logger::class);
        $exception = $this->throwTestException();

        $o = new ErrorHandler($logger);
        $resp = $o->handle($request, $exception, true);
        $this->assertEquals(500, $resp->getStatusCode());
    }


    public function testHandleHttpException(): void
    {
        $request = $this->getMockInstance(ServerRequest::class, [
            'getMethod' => 'POST'
        ]);
        $logger = $this->getMockInstance(Logger::class);
        $exception = $this->throwTestHttpException();

        $o = new ErrorHandler($logger);
        $resp = $o->handle($request, $exception, true);
        $this->assertEquals(404, $resp->getStatusCode());
        $this->assertEquals('bar', $resp->getHeaderLine('foo'));
    }

    public function testHandleHttpMethodNotAllowedException(): void
    {
        $request = $this->getMockInstance(ServerRequest::class, [
            'getMethod' => 'GET'
        ]);
        $logger = $this->getMockInstance(Logger::class);
        $exception = $this->throwTestHttpMethodNotAllowedException();

        $o = new ErrorHandler($logger);
        $resp = $o->handle($request, $exception, true);
        $this->assertEquals(405, $resp->getStatusCode());
        $this->assertEquals('PUT, POST', $resp->getHeaderLine('Allow'));
    }

    public function testHandleCorsPreflighRequest(): void
    {
        $request = $this->getMockInstance(ServerRequest::class, [
            'getMethod' => 'OPTIONS'
        ]);
        $logger = $this->getMockInstance(Logger::class);
        $exception = $this->throwTestHttpException();

        $o = new ErrorHandler($logger);
        $resp = $o->handle($request, $exception, true);
        $this->assertEquals(200, $resp->getStatusCode());
    }

    public function testHandleUsingRequestContentType(): void
    {
        $request = $this->getMockInstanceMap(ServerRequest::class, [
            'getHeaderLine' => [
                ['Accept', 'application/xml']
            ]
        ]);
        $logger = $this->getMockInstance(Logger::class);
        $exception = $this->throwTestHttpException();

        $o = new ErrorHandler($logger);
        $resp = $o->handle($request, $exception, true);
        $this->assertEquals(404, $resp->getStatusCode());
        $this->assertEquals('application/xml', $resp->getHeaderLine('Content-Type'));
    }

    public function testHandleUsingMutlipleRequestContentType(): void
    {
        $request = $this->getMockInstanceMap(ServerRequest::class, [
            'getHeaderLine' => [
                ['Accept', 'text/plain, application/xml']
            ]
        ]);
        $logger = $this->getMockInstance(Logger::class);
        $exception = $this->throwTestHttpException();

        $o = new ErrorHandler($logger);
        $resp = $o->handle($request, $exception, true);
        $this->assertEquals(404, $resp->getStatusCode());
        $this->assertEquals('application/xml', $resp->getHeaderLine('Content-Type'));
    }

    public function testHandleUsingExtendedRequestContentType(): void
    {
        $request = $this->getMockInstanceMap(ServerRequest::class, [
            'getHeaderLine' => [
                ['Accept', 'application/+xml']
            ]
        ]);
        $logger = $this->getMockInstance(Logger::class);
        $exception = $this->throwTestHttpException();

        $o = new ErrorHandler($logger);
        $resp = $o->handle($request, $exception, true);
        $this->assertEquals(404, $resp->getStatusCode());
        $this->assertEquals('application/xml', $resp->getHeaderLine('Content-Type'));
    }

    public function testHandleNoContentTypeRenderer(): void
    {
        $renderer = $this->getMockInstance(TextPlainErrorRenderer::class);
        $request = $this->getMockInstance(ServerRequest::class);
        $logger = $this->getMockInstance(Logger::class);
        $exception = $this->throwTestException();

        $o = new ErrorHandler($logger);
        $o->setDefaultErrorRenderer('text/plain', $renderer);
        $o->setContentType('myfoo-contentype');
        $resp = $o->handle($request, $exception, true);
        $this->assertEquals(500, $resp->getStatusCode());
    }

    public function testHandleCustomContentType(): void
    {
        $request = $this->getMockInstance(ServerRequest::class, [
            'getMethod' => 'GET'
        ]);
        $renderer = $this->getMockInstance(TextPlainErrorRenderer::class, [
            'render' => 'my renderer'
        ]);
        $logger = $this->getMockInstance(Logger::class);
        $exception = $this->throwTestException();
        $file = $this->getExceptionThrownFilePath(false);
        $o = new ErrorHandler($logger);
        $o->setDefaultErrorRenderer('text/plain', $renderer);
        $resp = $o->handle($request, $exception, true);
        $this->assertEquals(500, $resp->getStatusCode());
        $expected = 'Application Error
Type: Exception
Code: 100
Message: Foo exception 2
File: ' . $file . '
Line: 23

Previous Error:
Type: Exception
Code: 0
Message: Foo exception 1
File: ' . $file . '
Line: 20
';
        
        $this->assertEquals($expected, (string) $resp->getBody());
    }
}
