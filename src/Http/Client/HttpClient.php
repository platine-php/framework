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
    protected bool $verifySslCertificate = false;

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
}
