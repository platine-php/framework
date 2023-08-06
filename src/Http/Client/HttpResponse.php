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
 * Copyright (c) 2011 - 2017 rehyved.com
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
 *  @file HttpResponse.php
 *
 *  The Http Response class
 *
 *  @package    Platine\Framework\Http\Client
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Http\Client;

use Platine\Stdlib\Helper\Json;
use Platine\Stdlib\Helper\Xml;

/**
 * @class HttpResponse
 * @package Platine\Framework\Http\Client
 */
class HttpResponse
{
    /**
     * The request URL
     * @var string
     */
    protected string $url;

    /**
     * The HTTP status code
     * @var int
     */
    protected int $statusCode;

    /**
     * The HTTP headers size
     * @var int
     */
    protected int $headerSize = 0;

    /**
     * The response headers
     * @var array<string, mixed>
     */
    protected array $headers = [];

    /**
     * The response cookies
     * @var array<string, HttpCookie>
     */
    protected array $cookies = [];

    /**
     * The content type
     * @var string
     */
    protected string $contentType = '';

    /**
     * The response content length
     * @var int
     */
    protected int $contentLength = 0;

    /**
     * The response raw content
     * @var string
     */
    protected string $content = '';

    /**
     * The response error
     * @var string
     */
    protected string $error = '';

    /**
     * Create new instance
     * @param array<string, mixed> $requestInfo
     * @param array<string, mixed> $headers
     * @param mixed $response
     * @param string $error
     */
    public function __construct(
        array $requestInfo,
        array $headers,
        $response,
        string $error
    ) {
        $this->url = $requestInfo['url'];
        $this->statusCode = (int) $requestInfo['http_code'];
        $this->headers = $headers;
        $this->cookies = $this->processCookies($headers);

        if ($response !== false) {
            $this->contentType = $requestInfo['content_type'];
            $this->headerSize = (int) $requestInfo['header_size'];
            $this->contentLength = ((int) $requestInfo['content_length']) ?? (strlen($response) - $this->headerSize);
            $this->content = $this->processContent($response, $this->headerSize);
        }

        $this->error = $error;
    }

    /**
     * Return the request URL
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Return the HTTP status code
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Return the header size
     * @return int
     */
    public function getHeaderSize(): int
    {
        return $this->headerSize;
    }

    /**
     * Return the headers
     * @return array<string, mixed>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Return the HTTP header
     * @param string $name
     * @return mixed|null
     */
    public function getHeader(string $name)
    {
        return $this->headers[$name] ?? null;
    }

    /**
     * Return the cookies
     * @return array<string, HttpCookie>
     */
    public function getCookies(): array
    {
        return $this->cookies;
    }

    /**
     * Return the HTTP Cookie
     * @param string $name
     * @return HttpCookie|null
     */
    public function getCookie(string $name): ?HttpCookie
    {
        return $this->cookies[$name] ?? null;
    }

    /**
     * Return the response content type
     * @return string
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * Return the response content length
     * @return int
     */
    public function getContentLength(): int
    {
        return $this->contentLength;
    }

    /**
     * Return the raw response content
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Return the response as JSON
     * @param bool $assoc
     * @param int $depth
     * @param int $options
     * @return array<mixed>|object
     */
    public function json(
        bool $assoc = false,
        int $depth = 512,
        int $options = 0
    ) {
        return Json::decode($this->content, $assoc, $depth, $options);
    }

    /**
     * Return the response as XML
     * @return mixed
     */
    public function xml()
    {
        return Xml::decode($this->content);
    }

    /**
     * Whether the HTTP response indicated success or failure
     * @return bool
     */
    public function isError(): bool
    {
        return !empty($this->error) || HttpStatus::isError($this->statusCode);
    }


    /**
     * Process the cookies headers
     * @param array $headers
     * @return array<string, HttpCookie>
     */
    protected function processCookies(array $headers): array
    {
        $cookies = [];
        foreach ($headers as $header => $value) {
            if (stripos($header, 'Set-Cookie') !== false) {
                foreach ($value as $cookieString) {
                    $cookie = new HttpCookie($cookieString);
                    $cookies[$cookie->getName()] = $cookie;
                }
            }
        }

        return $cookies;
    }

    /**
     * Process the response content
     * @param string $response
     * @param int $headerSize
     * @return string
     */
    protected function processContent(string $response, int $headerSize): string
    {
        return substr($response, $headerSize);
    }
}
