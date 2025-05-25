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
 * @file Link.php
 *
 * The Graph Link class
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
 * @class Link
 * @package Platine\Framework\Helper\Mermaid\Graph
 */
class Link implements Stringable
{
    /**
     * Constants
    */
    public const ARROW  = 1;
    public const LINE   = 2;
    public const DOTTED = 3;
    public const THICK  = 4;

    /**
     * The link template
     */
    protected const TEMPLATES = [
        self::ARROW  => ['-->', '-->|%s|'],
        self::LINE   => [' --- ', '---|%s|'],
        self::DOTTED => ['-.->', '-. %s .-> '],
        self::THICK  => [' ==> ', ' == %s ==> '],
    ];

    /**
     * The source node
     * @var Node
     */
    protected Node $sourceNode;

    /**
     * The target node
     * @var Node
     */
    protected Node $targetNode;

    /**
     * The link style
     * @var int
     */
    protected int $style = self::ARROW;

    /**
     * The link text
     * @var string
     */
    protected string $text = '';

    /**
     * Create new instance
     * @param Node $source
     * @param Node $target
     * @param string $text
     * @param int $style
     */
    public function __construct(
        Node $source,
        Node $target,
        string $text = '',
        int $style = self::ARROW
    ) {
        $this->sourceNode = $source;
        $this->targetNode = $target;
        $this->text = $text;
        $this->style = $style;
    }

    /**
     * Set the link style
     * @param int $style
     * @return $this
     */
    public function setStyle(int $style): self
    {
        $this->style = $style;
        return $this;
    }

    /**
     * Return the link string representation
     * @return string
     */
    public function __toString(): string
    {
        $line = self::TEMPLATES[$this->style][0];
        if (!empty($this->text)) {
            $line = sprintf(
                self::TEMPLATES[$this->style][1],
                Helper::escape($this->text)
            );
        }

        return sprintf(
            '%s%s%s;',
            $this->sourceNode->getId(),
            $line,
            $this->targetNode->getId()
        );
    }
}
