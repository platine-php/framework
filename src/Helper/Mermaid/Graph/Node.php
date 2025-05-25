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
 * @file Node.php
 *
 * The Graph Node class
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

use Stringable;

/**
 * @class Node
 * @package Platine\Framework\Helper\Mermaid\Graph
 */
class Node implements Stringable
{
    /**
     * Constants
    */
    public const SQUARE            = '[%s]';
    public const ROUND             = '(%s)';
    public const CIRCLE            = '((%s))';
    public const ASYMMETRIC_SHAPE  = '>%s]';
    public const RHOMBUS           = '{%s}';
    public const HEXAGON           = '{{%s}}';
    public const PARALLELOGRAM     = '[/%s/]';
    public const PARALLELOGRAM_ALT = '[\%s\]';
    public const TRAPEZOID         = '[/%s\]';
    public const TRAPEZOID_ALT     = '[\%s/]';
    public const DATABASE          = '[(%s)]';
    public const SUBROUTINE        = '[[%s]]';
    public const STADIUM           = '([%s])';

    /**
     * The node id
     * @var string
     */
    protected string $id = '';

    /**
     * The node title
     * @var string
     */
    protected string $title = '';

    /**
     * The node form
     * @var string
     */
    protected string $form = self::ROUND;

    /**
     * Create new instance
     * @param string $id
     * @param string $title
     * @param string $form
     */
    public function __construct(
        string $id,
        string $title = '',
        string $form = self::ROUND
    ) {
        $this->id = Helper::getId($id);
        $this->setTitle($title ?: $id);
        $this->form = $form;
    }

    /**
     * Return the node id
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Set the node title
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Return the string representation
     * @return string
     */
    public function __toString(): string
    {
        if (!empty($this->title)) {
            return $this->id . sprintf(
                $this->form,
                Helper::escape($this->title)
            ) . ';';
        }

        return $this->id . ';';
    }
}
