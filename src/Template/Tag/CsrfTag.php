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
 *  @file CsrfTag.php
 *
 *  The CSRF generation template tag class
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
use Platine\Framework\Security\Csrf\CsrfManager;
use Platine\Template\Parser\AbstractTag;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Parser;

/**
 * @class CsrfTag
 * @package Platine\Framework\Template\Tag
 * @template T
 */
class CsrfTag extends AbstractTag
{
    /**
    * {@inheritdoc}
    */
    public function __construct(string $markup, &$tokens, Parser $parser)
    {
        parent::__construct($markup, $tokens, $parser);
        $this->extractAttributes($markup);
    }

    /**
     * {@inheritdoc}
     */
    public function render(Context $context): string
    {
        /** @template T @var Config<T> $config */
        $config = app(Config::class);

        /** @var CsrfManager<T> $csrfManager */
        $csrfManager = app(CsrfManager::class);

        $query = array_key_exists('query', $this->attributes);

        if ($query) {
            return http_build_query($csrfManager->getTokenQuery());
        }

        $key = $config->get('security.csrf.key', '');
        $token = $csrfManager->getToken();

        return sprintf(
            '<input type = "hidden" name = "%s" value = "%s" />',
            $key,
            $token
        );
    }
}
