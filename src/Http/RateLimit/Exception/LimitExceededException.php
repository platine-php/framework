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

namespace Platine\Framework\Http\RateLimit\Exception;

use Platine\Framework\Http\RateLimit\Rate;
use RuntimeException;

/**
 * @class LimitExceededException
 * @package Platine\Framework\Http\RateLimit\Exception
 */
class LimitExceededException extends RuntimeException
{
    /**
     * The identifier
     * @var string
     */
    protected string $identifier;

    /**
     * The rate used
     * @var Rate
     */
    protected Rate $rate;


    /**
     * Create exception for limit reached
     * @param string $identifier
     * @param Rate $rate
     * @return self
     */
    public static function create(string $identifier, Rate $rate): self
    {
        $exception = new self(sprintf(
            'Limit has been exceeded for identifier "%s".',
            $identifier
        ));

        $exception->identifier = $identifier;
        $exception->rate = $rate;

        return $exception;
    }

    /**
     * Return the identifier
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Return the rate
     * @return Rate
     */
    public function getRate(): Rate
    {
        return $this->rate;
    }
}
