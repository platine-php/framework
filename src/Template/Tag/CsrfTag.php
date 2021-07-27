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
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Template\Tag;

use Platine\Config\Config;
use Platine\Session\Session;
use Platine\Stdlib\Helper\Str;
use Platine\Template\Parser\AbstractTag;
use Platine\Template\Parser\Context;

/**
 * @class CsrfTag
 * @package Platine\Framework\Template\Tag
 */
class CsrfTag extends AbstractTag
{

    /**
     * {@inheritdoc}
     */
    public function render(Context $context): string
    {
        /** @template T @var Config<T> $config */
        $config = app(Config::class);

        /** @var Session $session */
        $session = app(Session::class);

        $currentTime = time();

        if (
            $session->has('csrf_data.value')
            && $session->has('csrf_data.expire')
            && $session->get('csrf_data.expire') > $currentTime
        ) {
            $value = $session->get('csrf_data.value');
        } else {
            $value = sha1(Str::randomToken(24));
            $expire = $config->get('security.csrf.expire', 300);
            $newTime = time() + $expire;

            $data = [
                'expire' => $newTime,
                'value' => $value,
            ];

            $session->set('csrf_data', $data);
        }

        $key = $config->get('security.csrf.key', '');

        return sprintf(
            '<input type = "hidden" name = "%s" value = "%s" />',
            $key,
            $value
        );
    }
}
