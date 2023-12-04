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

use InvalidArgumentException;
use Platine\Framework\Http\Client\Exception\HttpClientException;
use Platine\Stdlib\Helper\Json;

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
     * @param array<string, mixed> $headers
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
     * Set the request content type as JSON
     * @return $this
     */
    public function json(): self
    {
        $this->contentType('application/json');

        return $this;
    }

    /**
     * Set the request content type as form
     * @return $this
     */
    public function form(): self
    {
        $this->contentType('application/x-www-form-urlencoded');

        return $this;
    }

    /**
     * Set the request content type as multipart
     * @return $this
     */
    public function mutlipart(): self
    {
        $this->contentType('multipart/form-data');

        return $this;
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

    /**
     * Execute the request as a GET request to the specified path
     * @param string $path
     * @return HttpResponse
     */
    public function get(string $path = ''): HttpResponse
    {
        return $this->request($path, HttpMethod::GET);
    }

    /**
     * Execute the request as a POST request to the specified path
     * @param string $path
     * @param mixed|null $body the request body
     * @return HttpResponse
     */
    public function post(string $path = '', $body = null): HttpResponse
    {
        return $this->request($path, HttpMethod::POST, $body);
    }

    /**
     * Execute the request as a PUT request to the specified path
     * @param string $path
     * @param mixed|null $body the request body
     * @return HttpResponse
     */
    public function put(string $path = '', $body = null): HttpResponse
    {
        return $this->request($path, HttpMethod::PUT, $body);
    }

    /**
     * Execute the request as a DELETE request to the specified path
     * @param string $path
     * @param mixed|null $body the request body
     * @return HttpResponse
     */
    public function delete(string $path = '', $body = null): HttpResponse
    {
        return $this->request($path, HttpMethod::DELETE, $body);
    }

    /**
     * Execute the request as a HEAD request to the specified path
     * @param string $path
     * @param mixed|null $body the request body
     * @return HttpResponse
     */
    public function head(string $path = '', $body = null): HttpResponse
    {
        return $this->request($path, HttpMethod::HEAD, $body);
    }

    /**
     * Execute the request as a TRACE request to the specified path
     * @param string $path
     * @param mixed|null $body the request body
     * @return HttpResponse
     */
    public function trace(string $path = '', $body = null): HttpResponse
    {
        return $this->request($path, HttpMethod::TRACE, $body);
    }

    /**
     * Execute the request as a OPTIONS request to the specified path
     * @param string $path
     * @param mixed|null $body the request body
     * @return HttpResponse
     */
    public function options(string $path = '', $body = null): HttpResponse
    {
        return $this->request($path, HttpMethod::OPTIONS, $body);
    }

    /**
     * Execute the request as a CONNECT request to the specified path
     * @param string $path
     * @param mixed|null $body the request body
     * @return HttpResponse
     */
    public function connect(string $path = '', $body = null): HttpResponse
    {
        return $this->request($path, HttpMethod::CONNECT, $body);
    }

    /**
     * Construct the HTTP request and sends it using the provided method and request body
     * @param string $path
     * @param string $method
     * @param mixed|null $body
     * @return HttpResponse
     */
    public function request(string $path, string $method = HttpMethod::GET, $body = null): HttpResponse
    {
        $ch = curl_init();

        $this->processUrl($path, $ch);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        // Do body first as this might add additional headers
        $this->processBody($ch, $body);
        $this->processHeaders($ch);
        $this->processCookies($ch);

        return $this->send($ch);
    }

    /**
     * Send the request
     * @param mixed $ch the cURL handle
     * @return HttpResponse
     */
    protected function send($ch): HttpResponse
    {
        $responseHeaders = [];
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($curl, $header) use (&$responseHeaders) {
            if (strpos($header, ':') !== false) {
                list($name, $value) = explode(':', $header);
                if (array_key_exists($name, $responseHeaders) === false) {
                    $responseHeaders[$name] = [];
                }
                $responseHeaders[$name][] = trim($value);
            }

            return strlen($header);
        });

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Ensure we are coping with 300 (redirect) responses
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);

        // Set request timeout
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        // Set verification of SSL certificates
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verifySslCertificate);

        if (!empty($this->username)) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, sprintf('%s:%s', $this->username, $this->password));
        }

        $response = curl_exec($ch);
        $requestInfo = curl_getinfo($ch);
        $error = curl_error($ch);
        $errorCode = curl_errno($ch);
        if (!empty($error)) {
            throw new HttpClientException($error, $errorCode);
        }

        return new HttpResponse($requestInfo, $responseHeaders, $response, $error);
    }

    /**
     * Process URL
     * @param string $path
     * @param mixed $ch the cURL handle
     * @return void
     */
    protected function processUrl(string $path, $ch): void
    {
        $url = $this->buildUrl($path);
        curl_setopt($ch, CURLOPT_URL, $url);
    }

    /**
     * Build the request full URL
     * @param string $path
     * @return string
     */
    protected function buildUrl(string $path): string
    {
        if (empty($this->baseUrl)) {
            throw new InvalidArgumentException('Base URL can not be empty or null');
        }

        $url = $this->baseUrl;
        if (!empty($path)) {
            $url .= $path;
        }

        if (count($this->parameters) > 0) {
            $url .= '?' . http_build_query($this->parameters);
        }

        // Clean url
        // remove double slashes, except after scheme
        $cleanUrl = (string) preg_replace('/([^:])(\/{2,})/', '$1/', $url);
        // convert arrays with indexes to arrays without
        // (i.e. parameter[0]=1 -> parameter[]=1)
        $finalUrl = (string) preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', $cleanUrl);

        return $finalUrl;
    }

    /**
     * Process the request headers
     * @param mixed $ch the cURL handle
     * @return void
     */
    protected function processHeaders($ch): void
    {
        $headers = [];
        foreach ($this->headers as $name => $values) {
            foreach ($values as $value) {
                $headers[] = sprintf('%s: %s', $name, $value);
            }
        }

        if (count($headers) > 0) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
    }

    /**
     * Process the request cookies
     * @param mixed $ch the cURL handle
     * @return void
     */
    protected function processCookies($ch): void
    {
        $cookies = [];
        foreach ($this->cookies as $name => $value) {
            $cookies[] = sprintf('%s=%s', $name, $value);
        }
        if (count($cookies) > 0) {
            curl_setopt($ch, CURLOPT_COOKIE, implode(';', $cookies));
        }
    }

    /**
     * Process the request body
     * @param mixed $ch the cURL handle
     * @param array<mixed>|object|null $body the request body
     * @return void
     */
    protected function processBody($ch, $body = null): void
    {
        if ($body === null) {
            return;
        }

        if (isset($this->headers['Content-Type'][0])) {
            $contentType = $this->headers['Content-Type'][0];
            if (stripos($contentType, 'application/json') !== false) {
                $body = Json::encode($body);
            } elseif (stripos($contentType, 'application/x-www-form-urlencoded') !== false) {
                $body = http_build_query($body);
            } elseif (stripos($contentType, 'multipart/form-data') !== false) {
                $boundary = $this->parseBoundaryFromContentType($contentType);
                $body = $this->buildMultipartBody(/** @var array<mixed> $body */ $body, $boundary);
            }
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }

    /**
     * Parse boundary from request content type
     * @param string $contentType
     * @return string
     */
    protected function parseBoundaryFromContentType(string $contentType): string
    {
        $match = [];
        if (preg_match('/boundary="([^\"]+)"/is', $contentType, $match) > 0) {
            return $match[1];
        }

        throw new InvalidArgumentException(
            'The provided Content-Type header contained a "multipart/*" content type but did not '
                . 'define a boundary.'
        );
    }

    /**
     * Build the multipart body
     * @param array<string, mixed> $fields
     * @param string $boundary
     * @return string
     */
    protected function buildMultipartBody(array $fields, string $boundary): string
    {
        $body = '';
        foreach ($fields as $name => $value) {
            if (is_array($value)) {
                $data = $value['data'];
                $filename = $value['filename'] ?? false;

                $body .= sprintf("--%s\nContent-Disposition: form-data; name=\"%s\"", $boundary, $name);
                if ($filename !== false) {
                    $body .= sprintf(";filename=\"%s\"", $filename);
                }

                $body .= sprintf("\n\n%s\n", $data);
            } elseif (!empty($value)) {
                $body .= sprintf(
                    "--%s\nContent-Disposition: form-data; name=\"%s\"\n\n%s\n",
                    $boundary,
                    $name,
                    $value
                );
            }
        }

        $body .= sprintf('--%s--', $boundary);

        return $body;
    }
}
