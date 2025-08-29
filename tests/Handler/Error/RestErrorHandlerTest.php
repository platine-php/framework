<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Handler\Error;

use Platine\Framework\Handler\Error\RestErrorHandler;
use Platine\Framework\Http\Response\JsonResponse;
use Platine\Http\ServerRequest;
use Platine\Logger\Logger;

class RestErrorHandlerTest extends BaseErrorHandlerTestCase
{
    public function testHandleHttpMethodNotAllowedException(): void
    {
        $request = $this->getMockInstance(ServerRequest::class, [
            'getMethod' => 'GET'
        ]);
        $logger = $this->getMockInstance(Logger::class);
        $exception = $this->throwTestHttpMethodNotAllowedException();

        $o = new RestErrorHandler($logger);
        $resp = $o->handle($request, $exception, true);
        $this->assertEquals(405, $resp->getStatusCode());
        $this->assertEquals('PUT, POST', $resp->getHeaderLine('Allow'));
        $this->assertInstanceOf(JsonResponse::class, $resp);
    }
}
