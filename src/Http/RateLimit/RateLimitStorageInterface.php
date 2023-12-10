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
 *  @file RateLimitStorageInterface.php
 *
 *  The Rate Limit storage class
 *
 *  @package    Platine\Framework\Http\RateLimit
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Http\RateLimit;

/**
 * @class RateLimitStorageInterface
 * @package Platine\Framework\Http\RateLimit
 */
interface RateLimitStorageInterface
{
    /**
     * Store the value of the given key
     * @param string $key
     * @param float $value
     * @param int $ttl
     * @return bool
     */
    public function set(string $key, float $value, int $ttl): bool;

    /**
     * Return the value of the given key
     * @param string $key
     * @return float
     */
    public function get(string $key): float;

    /**
     * Whether the value of the given key exist
     * @param string $key
     * @return bool
     */
    public function exists(string $key): bool;

    /**
     * Delete the value of the given key
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool;
}
