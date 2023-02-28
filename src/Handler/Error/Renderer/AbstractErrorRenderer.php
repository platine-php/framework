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
 *  @file AbstractErrorRenderer.php
 *
 *  The base class for error renderer
 *
 *  @package    Platine\Framework\Handler\Error\Renderer
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Handler\Error\Renderer;

use Platine\Framework\Handler\Error\ErrorRenderInterface;
use Platine\Framework\Http\Exception\HttpException;
use Throwable;

/**
 * @class AbstractErrorRenderer
 * @package Platine\Framework\Handler\Error\Renderer
 */
abstract class AbstractErrorRenderer implements ErrorRenderInterface
{
    /**
     * The default error title
     * @var string
     */
    protected string $title = 'Application Error';

    /**
     * The default error description
     * @var string
     */
    protected string $description = 'An error has occurred. Sorry for the '
            . 'temporary inconvenience.';

    /**
     * Return the error title for the given exception if available
     * otherwise the default title will be returned
     * @param Throwable $exception
     * @return string
     */
    protected function getErrorTitle(Throwable $exception): string
    {
        if ($exception instanceof HttpException) {
            return $exception->getTitle();
        }

        return $this->title;
    }

    /**
     * Return the error description for the given exception if available
     * otherwise the default description will be returned
     * @param Throwable $exception
     * @return string
     */
    protected function getErrorDescription(Throwable $exception): string
    {
        if ($exception instanceof HttpException) {
            return $exception->getDescription();
        }

        return $this->description;
    }
}
