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

declare(strict_types=1);

namespace Platine\Framework\Http\Exception;

use Exception;
use Platine\Http\ServerRequestInterface;
use Throwable;

/**
 * @class HttpException
 * @package Platine\Framework\Http\Exception
 */
class HttpException extends Exception
{
    /**
     * The instance of server request that throw this exception
     * @var ServerRequestInterface
     */
    protected ServerRequestInterface $request;

    /**
     * The exception title
     * @var string
     */
    protected string $title = '';

    /**
     * The exception description
     * @var string
     */
    protected string $description = '';

    /**
     * Additional headers to add into error response
     * @var array<string, mixed>
     */
    protected array $headers = [];

    /**
     * Create new instance
     * @param ServerRequestInterface $request
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @param array<string, mixed> $headers
     */
    public function __construct(
        ServerRequestInterface $request,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        array $headers = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->request = $request;
        $this->headers = $headers;
    }

    /**
     * Return the server request
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * Return the title
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Return the description
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     *
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     *
     * @param string $description
     * @return $this
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Return headers
     * @return array<string, mixed>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Set the headers
     * @param array<string, mixed> $headers
     * @return $this
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }
}
