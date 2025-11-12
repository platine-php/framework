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
 *  @file RequestData.php
 *
 *  This class contains the methods to query request data
 *
 *  @package    Platine\Framework\Http
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Http;

use Platine\Http\ServerRequestInterface;
use Platine\Stdlib\Helper\Arr;

/**
 * @class RequestData
 * @package Platine\Framework\Http
 */
class RequestData
{
    /**
     * The request body or post data
     * @var array<string, mixed>
     */
    protected array $posts = [];

    /**
     * The request get data
     * @var array<string, mixed>
     */
    protected array $gets = [];

    /**
     * The request servers environment data
     * @var array<string, mixed>
     */
    protected array $servers = [];

    /**
     * The request cookies data
     * @var array<string, mixed>
     */
    protected array $cookies = [];

    /**
     * The request files data
     * @var array<string|int, mixed|UploadedFileInterface>
     */
    protected array $files = [];

    /**
     * Create new instance
     * @param ServerRequestInterface $request
     */
    public function __construct(ServerRequestInterface $request)
    {
        $this->posts = (array) $request->getParsedBody();
        $this->gets = $request->getQueryParams();
        $this->servers = $request->getServerParams();
        $this->cookies = $request->getCookieParams();
        $this->files = $request->getUploadedFiles();
    }

    /**
     * Return the post data
     * @param bool $autoEscape whether to apply XSS rule on inputs
     * @return array<string, mixed>
     */
    public function posts(bool $autoEscape = true): array
    {
        return $this->applyInputClean($this->posts, $autoEscape);
    }

    /**
     * Return the get data
     * @param bool $autoEscape whether to apply XSS rule on inputs
     * @return array<string, mixed>
     */
    public function gets(bool $autoEscape = true): array
    {
        return $this->applyInputClean($this->gets, $autoEscape);
    }

    /**
     * Return the files data
     * @return array<string|int, mixed|UploadedFileInterface>
     */
    public function files(): array
    {
        return $this->files;
    }

    /**
     * Return the server data
     * @param bool $autoEscape whether to apply XSS rule on inputs
     * @return array<string, mixed>
     */
    public function servers(bool $autoEscape = true): array
    {
        return $this->applyInputClean($this->servers, $autoEscape);
    }

    /**
     * Return the cookie data
     * @param bool $autoEscape whether to apply XSS rule on inputs
     * @return array<string, mixed>
     */
    public function cookies(bool $autoEscape = true): array
    {
        return $this->applyInputClean($this->cookies, $autoEscape);
    }

    /**
     * Return the request query value for the given key
     * @param string $key the key to fetch also support for dot notation
     * @param mixed $default
     * @param bool $autoEscape whether to apply XSS rule on inputs
     *
     * @return mixed
     */
    public function get(
        string $key,
        mixed $default = null,
        bool $autoEscape = true
    ): mixed {
        $gets = $this->applyInputClean($this->gets, $autoEscape);
        return Arr::get($gets, $key, $default);
    }

    /**
     * Return the request body or post value for the given key
     * @param string $key the key to fetch also support for dot notation
     * @param mixed $default
     * @param bool $autoEscape whether to apply XSS rule on inputs
     *
     * @return mixed
     */
    public function post(
        string $key,
        mixed $default = null,
        bool $autoEscape = true
    ): mixed {
        $posts = $this->applyInputClean($this->posts, $autoEscape);
        return Arr::get($posts, $key, $default);
    }

    /**
     * Return the request server value for the given key
     * @param string $key the key to fetch also support for dot notation
     * @param mixed $default
     * @param bool $autoEscape whether to apply XSS rule on inputs
     *
     * @return mixed
     */
    public function server(
        string $key,
        mixed $default = null,
        bool $autoEscape = true
    ): mixed {
        $servers = $this->applyInputClean($this->servers, $autoEscape);
        return Arr::get($servers, $key, $default);
    }

    /**
     * Return the request cookie value for the given key
     * @param string $key the key to fetch also support for dot notation
     * @param mixed $default
     * @param bool $autoEscape whether to apply XSS rule on inputs
     *
     * @return mixed
     */
    public function cookie(
        string $key,
        mixed $default = null,
        bool $autoEscape = true
    ): mixed {
        $cookies = $this->applyInputClean($this->cookies, $autoEscape);
        return Arr::get($cookies, $key, $default);
    }

    /**
     * Return the request uploaded file for the given key
     * @param string $key the key to fetch also support for dot notation
     *
     * @return mixed
     */
    public function file(string $key): mixed
    {
        $files = $this->files;
        return Arr::get($files, $key, null);
    }

    /**
     * Apply the input cleanup based on the status
     * @param array<mixed> $data
     * @param bool $autoEscape whether to apply XSS rule on inputs
     * @return array<mixed>
     */
    protected function applyInputClean(array $data, bool $autoEscape = true): array
    {
        if ($autoEscape) {
            $data = (new InputClean())->clean($data);
        }

        return $data;
    }
}
