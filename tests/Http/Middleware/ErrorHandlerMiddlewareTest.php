<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http\Middleware;

use Platine\Framework\Handler\Error\ErrorHandler;
use Platine\Framework\Http\Exception\HttpException;
use Platine\Framework\Http\Middleware\ErrorHandlerMiddleware;
use Platine\Framework\Kernel\HttpKernel;
use Platine\Http\ServerRequest;
use Platine\Logger\Logger;
use Platine\Test\Framework\Fixture\MyDefaultPhpErrorRequestHandler;
use Platine\Test\Framework\Fixture\MyResponse;
use Platine\Test\Framework\Handler\Error\BaseErrorHandlerTestCase;

/*
 * @group core
 * @group framework
 */
class ErrorHandlerMiddlewareTest extends BaseErrorHandlerTestCase
{
    public function testProcessErrorHttpException(): void
    {
        $logger = $this->getMockInstance(Logger::class);
        $request = $this->getMockInstance(ServerRequest::class);
        $handler = $this->getMockInstance(HttpKernel::class);

        $handler->expects($this->exactly(1))
                ->method('handle')
                ->will($this->throwException($this->throwTestHttpException()));

        $o = new ErrorHandlerMiddleware(true, $logger);
        $res = $o->process($request, $handler);

        $this->assertEquals(404, $res->getStatusCode());
    }

    public function testProcessCustomErrorHandler(): void
    {
        $errorResponse = new MyResponse();
        $errorResponse->getBody()->write('my error handler');
        $errorHandler = $this->getMockInstance(ErrorHandler::class, [
            'handle' => $errorResponse
        ]);
        $logger = $this->getMockInstance(Logger::class);
        $request = $this->getMockInstance(ServerRequest::class);
        $handler = $this->getMockInstance(HttpKernel::class);

        $handler->expects($this->exactly(1))
                ->method('handle')
                ->will($this->throwException($this->throwTestHttpException()));

        $o = new ErrorHandlerMiddleware(true, $logger);
        $o->setErrorHandler($errorHandler);
        $res = $o->process($request, $handler);

        $this->assertEquals(300, $res->getStatusCode());
        $this->assertEquals('my error handler', (string) $res->getBody());
    }

    public function testProcessErrorHandlerToExceptionWithSubTypeSupport(): void
    {
        $errorResponse = new MyResponse();
        $errorResponse->getBody()->write('my http error handler');
        $errorHandler = $this->getMockInstance(ErrorHandler::class, [
            'handle' => $errorResponse
        ]);
        $logger = $this->getMockInstance(Logger::class);
        $request = $this->getMockInstance(ServerRequest::class);
        $handler = $this->getMockInstance(HttpKernel::class);

        $handler->expects($this->exactly(1))
                ->method('handle')
                ->will($this->throwException($this->throwTestHttpException()));

        $o = new ErrorHandlerMiddleware(true, $logger);
        $o->addErrorHandler(HttpException::class, $errorHandler, true);
        $res = $o->process($request, $handler);

        $this->assertEquals(300, $res->getStatusCode());
        $this->assertEquals('my http error handler', (string) $res->getBody());
    }

    public function testProcessErrorHandlerToExceptionWithSubClasses(): void
    {
        $errorResponse = new MyResponse();
        $errorResponse->getBody()->write('my http error handler');
        $errorHandler = $this->getMockInstance(ErrorHandler::class, [
            'handle' => $errorResponse
        ]);
        $logger = $this->getMockInstance(Logger::class);
        $request = $this->getMockInstance(ServerRequest::class);
        $handler = $this->getMockInstance(HttpKernel::class);

        $handler->expects($this->exactly(1))
                ->method('handle')
                ->will($this->throwException(new HttpException($request)));

        $o = new ErrorHandlerMiddleware(true, $logger);
        $o->addErrorHandler(HttpException::class, $errorHandler, true);
        $res = $o->process($request, $handler);

        $this->assertEquals(300, $res->getStatusCode());
        $this->assertEquals('my http error handler', (string) $res->getBody());
    }

    public function testProcessErrorHandlerToExceptionWithoutSubTypeSupport(): void
    {
        $errorResponse = new MyResponse();
        $errorResponse->getBody()->write('my http error handler');
        $errorHandler = $this->getMockInstance(ErrorHandler::class, [
            'handle' => $errorResponse
        ]);
        $logger = $this->getMockInstance(Logger::class);
        $request = $this->getMockInstance(ServerRequest::class);
        $handler = $this->getMockInstance(HttpKernel::class);

        $handler->expects($this->exactly(1))
                ->method('handle')
                ->will($this->throwException(new HttpException($request)));

        $o = new ErrorHandlerMiddleware(false, $logger);
        $o->addErrorHandler(HttpException::class, $errorHandler, false);
        $res = $o->process($request, $handler);

        $this->assertEquals(300, $res->getStatusCode());
        $content = 'my http error handler';
        $this->assertEquals($content, (string) $res->getBody());
    }

    public function testProcessPhpError(): void
    {
        $logger = $this->getMockInstance(Logger::class);
        $request = $this->getMockInstance(ServerRequest::class);
        $handler = new MyDefaultPhpErrorRequestHandler();


        $o = new ErrorHandlerMiddleware(true, $logger);
        $res = $o->process($request, $handler);

        $this->assertEquals(500, $res->getStatusCode());
    }

    public function testProcessPhpErrorReportingDisable(): void
    {
        global $mock_error_reporting_to_zero;

        $mock_error_reporting_to_zero = true;

        $logger = $this->getMockInstance(Logger::class);
        $request = $this->getMockInstance(ServerRequest::class);
        $handler = new MyDefaultPhpErrorRequestHandler();


        $o = new ErrorHandlerMiddleware(true, $logger);
        $res = $o->process($request, $handler);

        $this->assertEquals(500, $res->getStatusCode());
    }
}
