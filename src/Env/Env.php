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
 * Copyright (c) 2017 Jitendra Adhikari
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
 *  @file Env.php
 *
 *  The Environment management class
 *
 *  @package    Platine\Framework\Env
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Env;

/**
 * @class Env
 * @package Platine\Framework\Env
 */
class Env
{
    /**
     * The custom filter validate used for array, duration values
     */
    protected const FILTER_VALIDATE_ARRAY = 111900;
    protected const FILTER_VALIDATE_DURATION = 111901;

    /**
     * Get the environment variable by its key/name.
     * @param string $key
     * @param mixed $default
     * @param string|null $filter
     * @param int|array<string, mixed> $options Additional options to filter.
     * @return mixed
     */
    public static function get(
        string $key,
        mixed $default = null,
        ?string $filter = null,
        int|array $options = 0
    ): mixed {
        if ($filter !== null) {
            $filterMaps = [
                'bool' => FILTER_VALIDATE_BOOLEAN,
                'int' => FILTER_VALIDATE_INT,
                'float' => FILTER_VALIDATE_FLOAT,
                'email' => FILTER_VALIDATE_EMAIL,
                'ip' => FILTER_VALIDATE_IP,
                'url' => FILTER_VALIDATE_URL,
                'array' => self::FILTER_VALIDATE_ARRAY, // special case
                'duration' => self::FILTER_VALIDATE_DURATION, // special case
            ];

            if (isset($filterMaps[$filter])) {
                $filter = $filterMaps[$filter];
            } else {
                $filter = null;
            }
        }
        $value = getenv($key);
        if ($value !== false) {
            return static::prepareValue($value, $filter, $options);
        }

        if (isset($_ENV[$key])) {
            return static::prepareValue($_ENV[$key], $filter, $options);
        }

        if (isset($_SERVER[$key])) {
            return static::prepareValue($_SERVER[$key], $filter, $options);
        }

        // Default is not passed through filter!

        return $default;
    }

    /**
     * Prepare the environment value
     * @param string $value
     * @param int|null $filter See http://php.net/filter_var
     * @param array<string, mixed>|int $options
     * @return mixed
     */
    protected static function prepareValue(
        string $value,
        ?int $filter = null,
        int|array $options = 0
    ): mixed {
        static $special = [
          'true' => true,
          'TRUE' => true,
          'false' => false,
          'FALSE' => false,
          'null' => null,
          'NULL' => null,
        ];

        $valueResolved = static::resolveReference($value);

        // strlen($valueResolved) < 6.
        if (!isset($valueResolved[5]) && array_key_exists($valueResolved, $special)) {
            return $special[$value];
        }

        if ($filter === self::FILTER_VALIDATE_ARRAY) {
            $separator = ',';
            if (is_array($options) && isset($options['separator'])) {
                $separator = $options['separator'];
            }

            return explode($separator, $value);
        }

        if ($filter === self::FILTER_VALIDATE_DURATION) {
            return static::getDurationValue($value);
        }

        if ($filter === null || function_exists('filter_var') === false) {
            return $valueResolved;
        }

        return filter_var($valueResolved, $filter, $options);
    }

    /**
     * Return the duration value
     * @param string $value
     * @return int
     */
    protected static function getDurationValue(string $value): int
    {
        $maps = [
            'ms' => fn($val) => (int)($val / 1000),
            's' => fn($val) => $val,
            'm' => fn($val) => $val * 60,
            'h' => fn($val) => $val * 3600,
            'd' => fn($val) => $val * 86400,
            'w' => fn($val) => $val * 604800,
        ];

        $val = strtolower($value);
        foreach ($maps as $unit => $cb) {
            if (strpos($val, $unit) !== false) {
                $val = str_replace($unit, '', $val);
                return $cb((int) $val);
            }
        }

        // default
        return (int) $value;
    }

    /**
     * Resolve variable reference like ${VAR_NAME}
     * @param string $value
     * @return string
     */
    protected static function resolveReference(string $value): string
    {
        if (empty($value) || strpos($value, '${') === false) {
            return $value;
        }

        $valueResolved = preg_replace_callback('~\$\{(\w+)\}~', function ($m) {
            return (null === $ref = static::get($m[1], null)) ? $m[0] : $ref;
        }, $value);

        if ($valueResolved !== null) {
            return $valueResolved;
        }

        return $value;
    }
}
