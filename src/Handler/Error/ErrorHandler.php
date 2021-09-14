<?php

/**
 * Platine Framework
 *
 * Platine Framework is a lightweight, high-performance, simple and elegant PHP
 * Web framework
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
 *  @file ErrorHandler.php
 *
 *  The default error handler class
 *
 *  @package    Platine\Framework\Handler\Error
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Handler\Error;

use Platine\Framework\Handler\Error\Renderer\HtmlErrorRenderer;
use Platine\Framework\Handler\Error\Renderer\JsonErrorRenderer;
use Platine\Framework\Handler\Error\Renderer\TextPlainErrorRenderer;
use Platine\Framework\Handler\Error\Renderer\XmlErrorRenderer;
use Platine\Framework\Http\Exception\HttpException;
use Platine\Framework\Http\Exception\HttpMethodNotAllowedException;
use Platine\Http\Response;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;
use Platine\Logger\LoggerInterface;
use Throwable;

/**
 * @class ErrorHandler
 * @package Platine\Framework\Handler\Error
 */
class ErrorHandler implements ErrorHandlerInterface
{

    /**
     * The content type
     * @var string
     */
    protected string $contentType = '';

    /**
     * The error renderer to use
     * @var ErrorRenderInterface
     */
    protected ErrorRenderInterface $renderer;

    /**
     * List of well known content type with their renderer
     * @var array<string, ErrorRenderInterface>
     */
    protected array $renderers = [];

    /**
     * The request method that generate this error
     * @var string
     */
    protected string $method;

    /**
     * The request
     * @var ServerRequestInterface
     */
    protected ServerRequestInterface $request;

    /**
     * The exception that is thrown
     * @var Throwable
     */
    protected Throwable $exception;

    /**
     * The HTTP status code to use for this error
     * @var int
     */
    protected int $statusCode;

    /**
     * The logger instance to use to save error
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * Whether to show error details
     * @var bool
     */
    protected bool $detail = true;

    /**
     * Create new instance
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;

        //Add default renderer
        $this->addDefaultsRenderer();
    }

    /**
     * Handle error and generate the response
     * @param ServerRequestInterface $request
     * @param Throwable $exception
     * @param bool $detail
     * @return ResponseInterface
     */
    public function handle(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $detail
    ): ResponseInterface {
        $this->detail = $detail;
        $this->request = $request;
        $this->exception = $exception;
        $this->method = $request->getMethod();
        $this->statusCode = $this->determineStatusCode();
        if (empty($this->contentType)) {
            $this->contentType = $this->getRequestContentType($request);
        }

        $this->saveErrorToLog();

        return $this->errorResponse();
    }

    /**
     * Set the content type to be used
     * @param string $contentType
     * @return $this
     */
    public function setContentType(string $contentType): self
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * Set error renderer for the given content type
     * @param string $contentType
     * @param ErrorRenderInterface $renderer
     * @return $this
     */
    public function addErrorRenderer(
        string $contentType,
        ErrorRenderInterface $renderer
    ): self {
        $this->renderers[$contentType] = $renderer;

        return $this;
    }

    /**
     * Set default error renderer
     * @param string $contentType
     * @param ErrorRenderInterface $renderer
     * @return $this
     */
    public function setDefaultErrorRenderer(
        string $contentType,
        ErrorRenderInterface $renderer
    ): self {
        $this->contentType = $contentType;
        $this->renderer = $renderer;

        return $this;
    }

    /**
     * Determine the error status code to be used
     * @return int
     */
    protected function determineStatusCode(): int
    {
        if ($this->method === 'OPTIONS') {
            return 200;
        }

        if ($this->exception instanceof HttpException) {
            return $this->exception->getCode();
        }

        return 500;
    }

    /**
     * Determine the error renderer to be used
     * @return ErrorRenderInterface
     */
    protected function determineRenderer(): ErrorRenderInterface
    {
        if (
            !empty($this->contentType)
            && array_key_exists($this->contentType, $this->renderers)
        ) {
            $renderer = $this->renderers[$this->contentType];
        } else {
            $renderer = $this->renderer;
        }

        return $renderer;
    }

    /**
     * Determine the content type based on the request
     * @param ServerRequestInterface $request
     * @return string
     */
    protected function getRequestContentType(ServerRequestInterface $request): string
    {
        $header = $request->getHeaderLine('Accept');
        $selected = array_intersect(
            explode(', ', $header),
            array_keys($this->renderers)
        );

        $count = count($selected);

        if ($count > 0) {
            $current = current($selected);

            /**
             * Ensure other supported content types take precedence over text/plain
             * when multiple content types are provided via Accept header.
             */
            if ($current === 'text/plain' && $count > 1) {
                $next = next($selected);
                if (is_string($next)) {
                    return $next;
                }
            }

            if (is_string($current)) {
                return $current;
            }
        }

        $matches = [];
        if (preg_match('/\+(json|xml)/', $header, $matches)) {
            $contentType = 'application/' . $matches[1];
            if (array_key_exists($contentType, $this->renderers)) {
                return $contentType;
            }
        }

        return 'text/html';
    }

    /**
     * Log the error
     * @return void
     */
    protected function saveErrorToLog(): void
    {
        $renderer = $this->determineRenderer();
        $error = $renderer->render($this->exception, $this->detail, true);

        $this->logError($error);
    }

    /**
     * Log error message
     * @param string $error the error to log
     * @return void
     */
    protected function logError(string $error): void
    {
        $this->logger->error($error);
    }

    /**
     * Return the response generated for this error
     * @return ResponseInterface
     */
    protected function errorResponse(): ResponseInterface
    {
        $response = new Response($this->statusCode);
        if (
            !empty($this->contentType)
            && array_key_exists($this->contentType, $this->renderers)
        ) {
            $response = $response->withHeader('Content-Type', $this->contentType);
        }

        if ($this->exception instanceof HttpMethodNotAllowedException) {
            $allowMethods = implode(', ', $this->exception->getAllowedMethods());
            $response = $response->withHeader('Allow', $allowMethods);
        }

        $renderer = $this->determineRenderer();

        $body = $renderer->render($this->exception, $this->detail, false);
        $response->getBody()->write($body);

        return $response;
    }

    /**
     * Add system defaults renderer
     * @return void
     */
    protected function addDefaultsRenderer(): void
    {
        $this->addErrorRenderer('text/html', new HtmlErrorRenderer());
        $this->addErrorRenderer('text/plain', new TextPlainErrorRenderer());
        $this->addErrorRenderer('application/xml', new XmlErrorRenderer());
        $this->addErrorRenderer('application/json', new JsonErrorRenderer());
    }
}
