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

/**
 *  @file StaticTag.php
 *
 *  The assets link template tag class
 *
 *  @package    Platine\Framework\Template\Tag
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Template\Tag;

use Platine\Config\Config;
use Platine\Template\Exception\ParseException;
use Platine\Template\Parser\AbstractTag;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Lexer;
use Platine\Template\Parser\Parser;
use Platine\Template\Parser\Token;

/**
 * @class StaticTag
 * @package Platine\Framework\Template\Tag
 */
class StaticTag extends AbstractTag
{
    /**
     * The static path
     * @var string
     */
    protected string $path;

    /**
    * {@inheritdoc}
    */
    public function __construct(string $markup, &$tokens, Parser $parser)
    {
        $lexer = new Lexer('/' . Token::QUOTED_FRAGMENT . '/');
        if ($lexer->match($markup)) {
            $this->path = $lexer->getStringMatch(0);
        } else {
            throw new ParseException(sprintf(
                'Syntax Error in "%s" - Valid syntax: static [PATH]',
                'static'
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function render(Context $context): string
    {
        /** @template T @var Config<T> $config */
        $config = app(Config::class);

        $baseUrl = $config->get('app.url', '');
        $baseUrl = rtrim($baseUrl, '/') . '/';
        $staticDir = $config->get('app.static_dir', 'static');
        $staticDir = rtrim($staticDir, '/') . '/';

        return sprintf(
            '%s%s%s',
            $baseUrl,
            $staticDir,
            $this->path
        );
    }
}
