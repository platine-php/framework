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
 *  @file HtmlErrorRenderer.php
 *
 *  The HTML error renderer class used to render the errors
 * for HTML content type
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

use Platine\Stdlib\Helper\Php;
use Throwable;

/**
 * @class HtmlErrorRenderer
 * @package Platine\Framework\Handler\Error\Renderer
 */
class HtmlErrorRenderer extends AbstractErrorRenderer
{
    /**
     * {@inheritdoc}
     */
    public function render(Throwable $exception, bool $detail, bool $isLog = false): string
    {
        if ($isLog) {
            return sprintf(
                '%s: %s, %s',
                $this->getErrorTitle($exception),
                $this->getErrorDescription($exception),
                Php::exceptionToString($exception, '', true)
            );
        }

        if ($detail) {
            $html = '<p>The application could not run because of the following error:</p>';
            $html .= '<h2>Details</h2>';
            $html .= $this->renderException($exception);
        } else {
            $html = sprintf('<p>%s</p>', $this->getErrorDescription($exception));
        }

        return $this->renderBody($this->getErrorTitle($exception), $html);
    }

    /**
     * Render exception data
     * @param Throwable $exception
     * @return string
     */
    protected function renderException(Throwable $exception): string
    {
        $html = sprintf(
            '<div><strong>Type:</strong> %s</div>',
            get_class($exception)
        );

        $code = $exception->getCode();
        if ($code !== null) {
            $html .= sprintf('<div><strong>Code:</strong> %s</div>', $code);
        }

        $message = $exception->getMessage();
        if ($message !== null) {
            $html .= sprintf(
                '<div><strong>Message:</strong> %s</div>',
                htmlentities($message)
            );
        }

        $file = $exception->getFile();
        if ($file !== null) {
            $html .= sprintf('<div><strong>File:</strong> %s</div>', $file);
        }

        $line = $exception->getLine();
        if ($line !== null) {
            $html .= sprintf('<div><strong>Line:</strong> %s</div>', $line);
        }

        $trace = $exception->getTraceAsString();
        if ($trace !== null) {
            $html .= '<h2>Trace</h2>';
            $html .= sprintf('<pre>%s</pre>', htmlentities($trace));
        }

        return $html;
    }

    /**
     * Render HTML body content
     * @param string $title
     * @param string $content
     * @return string
     */
    protected function renderBody(string $title = '', string $content = ''): string
    {
        return sprintf(
            '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">'
                . '<title>%s</title><style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana,sans-serif}'
                . 'h1{margin:0;font-size:48px;font-weight:normal;line-height:48px}'
                . 'strong{display:inline-block;width:65px}</style></head><body>'
                . '<h1>%s</h1><div>%s</div><a href="#" onClick="window.history.go(-1)">Go Back</a>'
                . '</body></html>',
            $title,
            $title,
            $content
        );
    }
}
