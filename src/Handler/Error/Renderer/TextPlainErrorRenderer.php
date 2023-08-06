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
 *  @file TextPlainErrorRenderer.php
 *
 *  The text plain error renderer class used to render the errors
 * for text plain content type
 *
 *  @package    Platine\Framework\Handler\Error\Renderer
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Handler\Error\Renderer;

use Throwable;

/**
 * @class TextPlainErrorRenderer
 * @package Platine\Framework\Handler\Error\Renderer
 */
class TextPlainErrorRenderer extends AbstractErrorRenderer
{
    /**
     * {@inheritdoc}
     */
    public function render(Throwable $exception, bool $detail, bool $isLog = false): string
    {
        $error = $this->getErrorTitle($exception) . "\n";

        if ($isLog) {
            return $error;
        }

        if ($detail) {
            $error .= $this->getExceptionText($exception);
            while ($exception = $exception->getPrevious()) {
                $error .= "\nPrevious Error:\n";
                $error .= $this->getExceptionText($exception);
            }
        }

        return $error;
    }

    /**
     * Render exception data
     * @param Throwable $exception
     * @return string
     */
    protected function getExceptionText(Throwable $exception): string
    {
        $error = sprintf("Type: %s\n", get_class($exception));
        $error .= sprintf("Code: %s\n", $exception->getCode());
        $error .= sprintf("Message: %s\n", $exception->getMessage());
        $error .= sprintf("File: %s\n", $exception->getFile());
        $error .= sprintf("Line: %s\n", $exception->getLine());

        return $error;
    }
}
