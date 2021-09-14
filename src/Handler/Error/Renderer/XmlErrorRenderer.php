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
 *  @file XmlErrorRenderer.php
 *
 *  The XML error renderer class used to render the errors
 * for xml content type
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

use Throwable;

/**
 * @class XmlErrorRenderer
 * @package Platine\Framework\Handler\Error\Renderer
 */
class XmlErrorRenderer extends AbstractErrorRenderer
{

    /**
     * {@inheritdoc}
     */
    public function render(Throwable $exception, bool $detail, bool $isLog = false): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<error>';
        $xml .= '<message>' . $this->addCharacterData($exception->getMessage()) . '</message>';

        if ($detail) {
            $xml .= '<exceptions>';
            do {
                $xml .= $this->getExceptionXml($exception);
            } while ($exception = $exception->getPrevious());
            $xml .= '</exceptions>';
        }
        $xml .= '</error>';

        return $xml;
    }

    /**
     * Render exception data
     * @param Throwable $exception
     * @return string
     */
    protected function getExceptionXml(Throwable $exception): string
    {

        $xml = '<exception>';
        $xml .= '<type>' . get_class($exception) . '</type>';
        $xml .= '<code>' . $exception->getCode() . '</code>';
        $xml .= '<message>' . $this->addCharacterData($exception->getMessage()) . '</message>';
        $xml .= '<file>' . $exception->getFile() . '</file>';
        $xml .= '<line>' . $exception->getLine() . '</line>';
        $xml .= '</exception>';

        return $xml;
    }

    /**
     * Add CDATA to the given string
     * @param string $value
     * @return string
     */
    protected static function addCharacterData(string $value): string
    {
        return sprintf('<![CDATA[%s]]>', $value);
    }
}
