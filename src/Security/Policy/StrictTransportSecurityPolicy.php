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
 * Copyright (c) 2015 - 2023 Paragon Initiative Enterprises
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
 *  @file StrictTransportSecurityPolicy.php
 *
 *  The Strict Transport Security Policy class
 *
 *  @package    Platine\Framework\Security\Policy
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Security\Policy;

/**
 * @class StrictTransportSecurityPolicy
 * @package Platine\Framework\Security\Policy
 */
class StrictTransportSecurityPolicy extends AbstractPolicy
{
    /**
     * {@inheritdoc}
     */
    public function headers(): string
    {
        $headers = [];
        $headers[] = $this->maxAge();

        $includeSubDomains = $this->configurations['include-sub-domains'] ?? false;
        if ($includeSubDomains) {
            $headers[] = 'includeSubDomains';
        }

        $preload = $this->configurations['preload'] ?? false;
        if ($preload) {
            $headers[] = 'preload';
        }

        return implode('; ', $headers);
    }

    /**
     * Return the max age directive
     * @return string
     */
    public function maxAge(): string
    {
        $maxAge = $this->configurations['max-age'] ?? 31536000;

        $age = (int) $maxAge;
        // prevent negative value
        $value = max($age, 0);

        return sprintf('max-age=%d', $value);
    }
}
