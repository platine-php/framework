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
 *  @file HttpClient.php
 *
 *  The Http Client class
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

/**
 * @class HttpClient
 * @package Platine\Framework\Http\Client
 */
class HttpClient
{
    /**
     * The base URL
     * @var string
     */
    protected string $baseUrl;

    /**
     * The request headers
     * @var array<string, array<int, mixed>>
     */
    protected array $headers = [];

    /**
     * The request parameters
     * @var array<string, mixed>
     */
    protected array $parameters = [];

    /**
     * The request cookies
     * @var array<string, mixed>
     */
    protected array $cookies = [];

    /**
     * Indicating the number of seconds to use as a timeout for HTTP requests
     * @var int
     */
    protected int $timeout = 30;

    /**
     * Indicating if the validity of SSL certificates should be enforced in HTTP requests
     * @var bool
     */
    protected bool $verifySslCertificate = true;

    /**
     * The username to use for basic authentication
     * @var string
     */
    protected string $username = '';

    /**
     * The password to use for basic authentication
     * @var string
     */
    protected string $password = '';

    /**
     * Create new instance
     * @param string $baseUrl
     */
    public function __construct(string $baseUrl = '')
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * Set the base URL
     * @param string $baseUrl
     * @return $this
     */
    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    /**
     * Return the base URL
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }


    /**
     * Add request header
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function header(string $name, $value): self
    {
        if (array_key_exists($name, $this->headers) === false) {
            $this->headers[$name] = [];
        }
        $this->headers[$name][] = $value;

        return $this;
    }

    /**
     * Add multiple request headers
     * @param array<int, array<string, mixed>> $headers
     * @return $this
     */
    public function headers(array $headers): self
    {
        foreach ($headers as $name => $value) {
            $this->header($name, $value);
        }

        return $this;
    }

    /**
     * Add request query parameter
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function parameter(string $name, $value): self
    {
        $this->parameters[$name] = $value;

        return $this;
    }


    /**
     * Add multiple request parameter
     * @param array<string, mixed> $parameters
     * @return $this
     */
    public function parameters(array $parameters): self
    {
        foreach ($parameters as $name => $value) {
            $this->parameter($name, $value);
        }

        return $this;
    }

    /**
     * Add request cookie
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function cookie(string $name, $value): self
    {
        $this->cookies[$name] = $value;

        return $this;
    }

    /**
     * Add multiple request cookie
     * @param array<string, mixed> $cookies
     * @return $this
     */
    public function cookies(?array $cookies = null): self
    {
        if ($cookies === null) {
            $cookies = $_COOKIE;
        }

        foreach ($cookies as $name => $value) {
            $this->cookie($name, $value);
        }

        return $this;
    }

    /**
     * Set the basic authentication to use on the request
     * @param string $usename
     * @param string $password
     * @return $this
     */
    public function basicAuthentication(string $usename, string $password = ''): self
    {
        $this->username = $usename;
        $this->password = $password;

        return $this;
    }

    /**
     * Set the request timeout
     * @param int $timeout
     * @return $this
     */
    public function timeout(int $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Controls if the validity of SSL certificates should be verified.
     * WARNING: This should never be done in a production setup and should be used for debugging only.
     * @param bool $verifySslCertificate
     * @return self
     */
    public function verifySslCertificate(bool $verifySslCertificate): self
    {
        $this->verifySslCertificate = $verifySslCertificate;

        return $this;
    }

    /**
     * Set request content type
     * @param string $contentType
     * @return $this
     */
    public function contentType(string $contentType): self
    {
       // If this is a multipart request and boundary was not defined,
       // we define a boundary as this is required for multipart requests.
        if (stripos($contentType, 'multipart/') !== false) {
            if (stripos($contentType, 'boundary') === false) {
                $contentType .= sprintf('; boundary="%s"', uniqid((string) time()));
                // remove double semi-colon, except after scheme
                $contentType = preg_replace('/(.)(;{2,})/', '$1;', $contentType);
            }
        }

        return $this->header('Content-Type', $contentType);
    }

    /**
     * Set request accept content type
     * @param string $contentType
     * @return $this
     */
    public function accept(string $contentType): self
    {
        return $this->header('Accept', $contentType);
    }

    /**
     * Set request authorization header
     * @param string $scheme the scheme to use in the value of the Authorization header (e.g. Bearer)
     * @param string $value the value to set for the the Authorization header
     * @return $this
     */
    public function authorization(string $scheme, string $value): self
    {
        return $this->header('Authorization', sprintf('%s %s', $scheme, $value));
    }

    /**
     * Return the headers
     * @return array<string, array<int, mixed>>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Return the parameters
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Return the cookies
     * @return array<string, mixed>
     */
    public function getCookies(): array
    {
        return $this->cookies;
    }

    /**
     * Return the timeout
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Whether to verify SSL certificate
     * @return bool
     */
    public function isVerifySslCertificate(): bool
    {
        return $this->verifySslCertificate;
    }

    /**
     * Return the username for basic authentication
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Return the password for basic authentication
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }
}
