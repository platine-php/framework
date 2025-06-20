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
 * Copyright (c) 2015 JBZoo Content Construction Kit (CCK)
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
 * @file Helper.php
 *
 * The Graph helper class
 *
 *  @package    Platine\Framework\Helper\Mermaid\Graph
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */
declare(strict_types=1);

namespace Platine\Framework\Helper\Mermaid\Graph;

/**
 * @class Helper
 * @package Platine\Framework\Helper\Mermaid\Graph
 */
class Helper
{
    /**
     * Escape the given text
     * @param string $text
     * @return string
     */
    public static function escape(string $text): string
    {
        $cleanText = trim($text);
        $cleanHtmltext = htmlentities($cleanText, ENT_COMPAT);
        $finalText = str_replace(['&', '#lt;', '#gt;'], ['#', '<', '>'], $cleanHtmltext);

        return sprintf('"%s"', $finalText);
    }

    /**
     * Return the id of the given text
     * @param string $str
     * @return string
     */
    public static function getId(string $str): string
    {
        return md5($str);
    }
}
