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
 *  @file HttpCookie.php
 *
 *  The Http Cookie class
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
 * @class HttpCookie
 * @package Platine\Framework\Http\Client
 */
class HttpCookie
{
    /**
     * The name of cookie
     * @var string
     */
    protected string $name;

    /**
     * The value of cookie
     * @var string
     */
    protected string $value;

    /**
     * The cookie expires time
     * @var int
     */
    protected int $expires = 0;

    /**
     * The cookie max age
     * @var int
     */
    protected int $maxAge = 0;

    /**
     * The cookie domain
     * @var string|null
     */
    protected ?string $domain = null;

    /**
     * The cookie path
     * @var string
     */
    protected string $path = '/';

    /**
     * Whether the cookie is secure or not
     * @var bool
     */
    protected bool $secure = false;

    /**
     * Whether the cookie is for HTTP or not
     * @var bool
     */
    protected bool $httpOnly = true;

    /**
     * The value of the same site
     *
     * @var string|null
     */
    protected ?string $sameSite = null;

    /**
     * Create new instance
     * @param string $cookieString
     */
    public function __construct(string $cookieString)
    {
        $parts = array_map('trim', explode(';', $cookieString));
        foreach ($parts as $partStr) {
            $part = array_map('trim', explode('=', $partStr));
            $name = $part[0];
            $value = $part[1] ?? '';
            if (stripos($name, 'Expires') !== false) {
                $this->expires = strtotime($value);
            } elseif (stripos($name, 'Max-Age') !== false) {
                $this->maxAge = intval($value);
            } elseif (stripos($name, 'Domain') !== false) {
                $this->domain = $value;
            } elseif (stripos($name, 'Path') !== false) {
                $this->path = $value;
            } elseif (stripos($name, 'Secure') !== false) {
                $this->secure = true;
            } elseif (stripos($name, 'HttpOnly') !== false) {
                $this->httpOnly = true;
            } elseif (stripos($name, 'SameSite') !== false) {
                $this->sameSite = $value;
            } else {
                $this->name = $name;
                $this->value = $value;
            }
        }
    }

    /**
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     *
     * @return int
     */
    public function getExpires(): int
    {
        return $this->expires;
    }

    /**
     *
     * @return int
     */
    public function getMaxAge(): int
    {
        return $this->maxAge;
    }

    /**
     *
     * @return string|null
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     *
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->secure;
    }

    /**
     *
     * @return bool
     */
    public function isHttpOnly(): bool
    {
        return $this->httpOnly;
    }

    /**
     *
     * @return string|null
     */
    public function getSameSite(): ?string
    {
        return $this->sameSite;
    }
}
