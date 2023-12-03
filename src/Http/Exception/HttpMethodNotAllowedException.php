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

/**
 * @class HttpMethodNotAllowedException
 * @package Platine\Framework\Http\Exception
 */
class HttpMethodNotAllowedException extends HttpSpecialException
{
    /**
     *
     * @var int
     */
    protected $code = 405;

    /**
     *
     * @var string
     */
    protected $message = 'Method not allowed.';

    /**
     * {@inheritdoc}
     */
    protected string $title = '405 Method Not Allowed';

    /**
     * {@inheritdoc}
     */
    protected string $description = 'The request method is not supported '
            . 'for the requested resource.';

    /**
     * The list of allowed methods
     * @var string[]
     */
    protected array $allowedMethods = [];

    /**
     * Return the list of allowed methods
     * @return string[]
     */
    public function getAllowedMethods(): array
    {
        return $this->allowedMethods;
    }

    /**
     * Set allowed methods
     * @param string[] $methods
     * @return $this
     */
    public function setAllowedMethods(array $methods): self
    {
        $this->allowedMethods = $methods;
        $this->message = sprintf(
            'Method not allowed. Must be one of: %s',
            implode(', ', $methods)
        );

        return $this;
    }
}
