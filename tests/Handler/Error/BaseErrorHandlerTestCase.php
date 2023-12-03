<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Handler\Error;

use Exception;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Http\Exception\HttpMethodNotAllowedException;
use Platine\Framework\Http\Exception\HttpNotFoundException;
use Platine\Http\ServerRequest;
use Throwable;

class BaseErrorHandlerTestCase extends PlatineTestCase
{
    protected function throwTestException(): Throwable
    {
        $exception = new Exception('Foo exception');
        try {
            throw new Exception('Foo exception 1');
        } catch (Exception $ex) {
            try {
                throw new Exception('Foo exception 2', 100, $ex);
            } catch (Exception $ex) {
                $exception = $ex;
            }
        }

        return $exception;
    }

    protected function throwTestHttpException(): Throwable
    {
        $exception = new Exception('Foo exception');
        try {
            throw new Exception('Foo exception 1');
        } catch (Exception $ex) {
            try {
                $e = new HttpNotFoundException(
                    $this->getMockInstance(ServerRequest::class),
                    'not found',
                    $ex
                );
                $e->setHeaders(['foo' => 'bar']);
                throw $e;
            } catch (Exception $ex) {
                $exception = $ex;
            }
        }

        return $exception;
    }

    protected function throwTestHttpMethodNotAllowedException(): Throwable
    {
        $exception = new Exception('Foo exception');
        try {
            throw new Exception('Foo exception 1');
        } catch (Exception $ex) {
            try {
                throw (new HttpMethodNotAllowedException(
                    $this->getMockInstance(ServerRequest::class),
                    'Method Not Allowed',
                    $ex
                ))->setAllowedMethods(['PUT', 'POST']);
                ;
            } catch (Exception $ex) {
                $exception = $ex;
            }
        }

        return $exception;
    }

    protected function getExceptionThrownFilePath(bool $fixWin = true): string
    {
        if ($fixWin) {
            return str_replace('\\', '\\\\', __FILE__);
        }

        return __FILE__;
    }
}
