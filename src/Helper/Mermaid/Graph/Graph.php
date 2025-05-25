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
 * @file Graph.php
 *
 * The Graph class
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
 * @class Graph
 * @package Platine\Framework\Helper\Mermaid\Graph
 */
class Graph implements Stringable
{
   /**
    * Constants
   */
    public const TOP_BOTTOM = 'TB';
    public const BOTTOM_TOP = 'BT';
    public const LEFT_RIGHT = 'LR';
    public const RIGHT_LEFT = 'RL';

   /**
    * Space for each sub graph
    */
    private const RENDER_SHIFT = 4;

   /**
    * The list of sub graph
    * @var array<Graph>
    */
    protected array $subGraphs = [];

   /**
    * The list of nodes
    * @var array<string, Node>
    */
    protected array $nodes = [];

   /**
    * The list of links
    * @var array<Link>
    */
    protected array $links = [];

   /**
    * The graph parameter
    * @var array<string, mixed>
    */
    protected array $params = [
       'title' => 'Graph',
       'direction' => self::LEFT_RIGHT,
    ];

   /**
    * Style list
    * @var array<string>
    */
    protected array $styles = [];

   /**
    * Create new instance
    * @param array<string, mixed> $params
    */
    public function __construct(array $params = [])
    {
        $this->setParams($params);
    }

   /**
    * Render the graph to string representation
    * @param bool $isMainGraph
    * @param int $shift
    * @return string
    */
    public function render(bool $isMainGraph = true, int $shift = 0): string
    {
        $spaces = str_repeat(' ', $shift);
        $spacesSub = str_repeat(' ', $shift + self::RENDER_SHIFT);
        /** @var array<string> */
        $result = [];
        if ($isMainGraph) {
            $result = [sprintf('graph %s;', $this->params['direction'])];
        } else {
            $result = [sprintf(
                '%ssubgraph %s',
                $spaces,
                Helper::escape($this->params['title'])
            )];
        }

        if (count($this->nodes) > 0) {
            $tmp = [];
            foreach ($this->nodes as $node) {
                $tmp[] = $spacesSub . $node;
            }
            $result = array_merge($result, $tmp);
            if ($isMainGraph) {
                $result[] = '';
            }
        }

        if (count($this->links) > 0) {
            $tmp = [];
            foreach ($this->links as $link) {
                $tmp[] = $spacesSub . $link;
            }
            $result = array_merge($result, $tmp);
            if ($isMainGraph) {
                $result[] = '';
            }
        }

        foreach ($this->subGraphs as $subGraph) {
            $result[] = $subGraph->render(false, $shift + 4);
        }

        if ($isMainGraph && count($this->styles) > 0) {
            foreach ($this->styles as $style) {
                $result[] = $spaces . $style . ';';
            }
        }

        if (!$isMainGraph) {
            $result[] = $spaces . 'end';
        }

        return implode(PHP_EOL, $result);
    }

   /**
    * Add new node
    * @param Node $node
    * @return $this
    */
    public function addNode(Node $node): self
    {
        $this->nodes[$node->getId()] = $node;

        return $this;
    }

   /**
    * Add new link
    * @param Link $link
    * @return $this
    */
    public function addLink(Link $link): self
    {
        $this->links[] = $link;

        return $this;
    }

   /**
    * Add new style
    * @param string $style
    * @return $this
    */
    public function addStyle(string $style): self
    {
        $this->styles[] = $style;

        return $this;
    }

   /**
    * Add new sub graph
    * @param Graph $subGraph
    * @return $this
    */
    public function addSubGraph(Graph $subGraph): self
    {
        $this->subGraphs[] = $subGraph;

        return $this;
    }

   /**
    * Return the string representation
    * @return string
    */
    public function __toString(): string
    {
        return $this->render();
    }


   /**
    * Return the parameters
    * @return array<string, mixed>
    */
    public function getParams(): array
    {
        return $this->params;
    }

   /**
    * Set the parameters
    * @param array<string, mixed> $params
    * @return $this
    */
    public function setParams(array $params)
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }

   /**
    * Return the list of sub graph
    * @return array<Graph>
    */
    public function getSubGraphs(): array
    {
        return $this->subGraphs;
    }

   /**
    * Return the list of node
    * @return array<string, Node>
    */
    public function getNodes(): array
    {
        return $this->nodes;
    }

   /**
    * Return the list of link
    * @return array<Link>
    */
    public function getLinks(): array
    {
        return $this->links;
    }

   /**
    * Return the list of style
    * @return array<string>
    */
    public function getStyles(): array
    {
        return $this->styles;
    }
}
