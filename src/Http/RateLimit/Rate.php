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
 *  @file Rate.php
 *
 *  The Rate class
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

use InvalidArgumentException;

/**
 * @class Rate
 * @package Platine\Framework\Http\RateLimit
 */
class Rate
{
    /**
     * The rate limit quota
     * @var int
     */
    protected int $quota;

    /**
     * The rate limit interval in seconds
     * @var int
     */
    protected int $interval;


    /**
     * Create new instance
     * @param int $quota
     * @param int $interval
     */
    final protected function __construct(int $quota, int $interval)
    {
        if ($quota <= 0) {
            throw new InvalidArgumentException(sprintf(
                'Quota must be greater than zero, received [%d]',
                $quota
            ));
        }

        if ($interval <= 0) {
            throw new InvalidArgumentException(sprintf(
                'Seconds interval must be greater than zero, received [%d]',
                $interval
            ));
        }

        $this->quota = $quota;
        $this->interval = $interval;
    }

    /**
     * Set quota to be used per second
     * @param int $quota
     * @return self
     */
    public static function perSecond(int $quota): self
    {
        return new static($quota, 1);
    }

     /**
     * Set quota to be used per minute
     * @param int $quota
     * @return self
     */
    public static function perMinute(int $quota): self
    {
        return new static($quota, 60);
    }

    /**
     * Set quota to be used per hour
     * @param int $quota
     * @return self
     */
    public static function perHour(int $quota): self
    {
        return new static($quota, 3600);
    }

    /**
     * Set quota to be used per day
     * @param int $quota
     * @return self
     */
    public static function perDay(int $quota): self
    {
        return new static($quota, 86400);
    }

    /**
     * Set custom quota and interval
     * @param int $quota
     * @param int $interval
     * @return self
     */
    public static function custom(int $quota, int $interval): self
    {
        return new static($quota, $interval);
    }

    /**
     * Return the rate limit quota
     * @return int
     */
    public function getQuota(): int
    {
        return $this->quota;
    }

    /**
     * Return the interval in second
     * @return int
     */
    public function getInterval(): int
    {
        return $this->interval;
    }
}
