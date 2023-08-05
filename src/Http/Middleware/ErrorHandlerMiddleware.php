<?php

/**
 * Platine Framework
 *
 * Platine Framework is a lightweight, high-performance, simple and elegant
 * PHP Web framework
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2020 Platine Framework
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/**
 *  @file ErrorHandlerMiddleware.php
 *
 *  The error handler middleware
 *
 *  @package    Platine\Framework\Http\Middleware
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Http\Middleware;

use ErrorException;
use Platine\Framework\Handler\Error\ErrorHandler;
use Platine\Framework\Handler\Error\ErrorHandlerInterface;
use Platine\Framework\Http\Exception\HttpException;
use Platine\Http\Handler\MiddlewareInterface;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Logger\LoggerInterface;
use Throwable;


/**
 * @class ErrorHandlerMiddleware
 * @package Platine\Framework\Http\Middleware
 */
class ErrorHandlerMiddleware implements MiddlewareInterface
{
    /**
     * The logger instance to use to save error
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * The default error handler to use
     * @var ErrorHandlerInterface
     */
    protected ErrorHandlerInterface $errorHandler;

    /**
     * Whether to show error details
     * @var bool
     */
    protected bool $detail = true;

    /**
     * List of error handler
     * @var array<string, ErrorHandlerInterface>
     */
    protected array $handlers = [];

    /**
     * List of sub classed error handlers
     * @var array<string, ErrorHandlerInterface>
     */
    protected array $subClassHandlers = [];

    /**
     * Create new instance
     * @param bool $detail
     * @param LoggerInterface $logger
     */
    public function __construct(bool $detail, LoggerInterface $logger)
    {
        $this->detail = $detail;
        $this->logger = $logger;
        $this->errorHandler = $this->getDefaultErrorHandler();
    }

    /**
     * Set error handler
     * @param ErrorHandlerInterface $errorHandler
     * @return $this
     */
    public function setErrorHandler(ErrorHandlerInterface $errorHandler): self
    {
        $this->errorHandler = $errorHandler;

        return $this;
    }

    /**
     * Add error handler for given type
     * @param string $type
     * @param ErrorHandlerInterface $handler
     * @param bool $useSubClasses
     * @return $this
     */
    public function addErrorHandler(
        string $type,
        ErrorHandlerInterface $handler,
        bool $useSubClasses = false
    ): self {
        if ($useSubClasses) {
            $this->subClassHandlers[$type] = $handler;
        } else {
            $this->handlers[$type] = $handler;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        set_error_handler(static function (
            int $severity,
            string $message,
            string $file,
            int $line
        ): bool {
            // https://www.php.net/manual/en/function.error-reporting.php#8866
            // Usages the defined levels of `error_reporting()`.
            if (!(error_reporting() & $severity)) {
                // This error code is not included in `error_reporting()`.
                return true;
            }

            throw new ErrorException($message, 0, $severity, $file, $line);
        });

        try {
            $response = $handler->handle($request);
        } catch (Throwable $exception) {
            $response = $this->handleException($request, $exception);
        }

        restore_error_handler();
        return $response;
    }

    /**
     * Handle exception
     * @param ServerRequestInterface $request
     * @param Throwable $exception
     * @return ResponseInterface
     */
    protected function handleException(
        ServerRequestInterface $request,
        Throwable $exception
    ): ResponseInterface {
        if ($exception instanceof HttpException) {
            $request = $exception->getRequest();
        }

        $type = get_class($exception);

        $handler = $this->getExceptionErrorHandler($type);

        return $handler->handle($request, $exception, $this->detail);
    }

    /**
     * Get the error handler for the given type
     * @param string $type
     * @return ErrorHandlerInterface
     */
    protected function getExceptionErrorHandler(string $type): ErrorHandlerInterface
    {
        if (isset($this->handlers[$type])) {
            return $this->handlers[$type];
        }

        if (isset($this->subClassHandlers[$type])) {
            return $this->subClassHandlers[$type];
        }

        foreach ($this->subClassHandlers as $class => $handler) {
            if (is_subclass_of($type, $class)) {
                return $handler;
            }
        }

        return $this->errorHandler;
    }

    /**
     * Return the default error handler if none is set before
     * @return ErrorHandlerInterface
     */
    protected function getDefaultErrorHandler(): ErrorHandlerInterface
    {
        return new ErrorHandler($this->logger);
    }
}
