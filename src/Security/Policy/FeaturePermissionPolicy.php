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
 *  @file FeaturePermissionPolicy.php
 *
 *  The Feature Security Policy class
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
 * @class FeaturePermissionPolicy
 * @package Platine\Framework\Security\Policy
 */
class FeaturePermissionPolicy extends AbstractPolicy
{
    /**
     * {@inheritdoc}
     */
    public function headers(): string
    {
        $headers = [];
        foreach ($this->configurations as $name => $config) {
            if ($name === 'enable') {
                continue;
            }

            $value = $this->directive($config);

            $headers[] = sprintf('%s=%s', $name, $value);
        }

        return implode(', ', $headers);
    }

    /**
     * Parse specific policy value
     * @param array<string, mixed> $config
     * @return string
     */
    public function directive(array $config): string
    {
        if ($config['none'] ?? false) {
            return '()';
        } elseif ($config['*'] ?? false) {
            return '*';
        }

        $origins = $this->origins($config['origins'] ?? []);
        if ($config['self'] ?? false) {
            array_unshift($origins, 'self');
        }

        return sprintf('(%s)', implode(' ', $origins));
    }

    /**
     * Get valid origins
     * @param array<string> $origins
     * @return array<string>
     */
    public function origins(array $origins): array
    {
        // prevent user leave spaces by mistake
        $cleanOrigins = array_map('trim', $origins);

        $filters = filter_var_array($cleanOrigins, FILTER_VALIDATE_URL);

        $values = array_filter($filters);

        // ensure indexes are numerically
        $urls = array_values($values);

        return array_map(function (string $url) {
            return sprintf('"%s"', $url);
        }, $urls);
    }
}
