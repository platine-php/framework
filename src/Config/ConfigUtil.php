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
 *  @file ConfigUtil.php
 *
 *  The Database Configuration utility class
 *
 *  @package    Platine\Framework\Config
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Config;

/**
 * @class ConfigUtil
 * @package Platine\Framework\Config
 */
class ConfigUtil
{
    /**
     * Convert the returned configuration to given type
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    public static function convertToDataType(mixed $value, string $type): mixed
    {
        switch ($type) {
            case 'integer':
                $value = intval($value);
                break;
            case 'double':
                $value = doubleval($value);
                break;
            case 'float':
                $value = floatval($value);
                break;
            case 'array':
            case 'object':
                $value = unserialize($value);
                break;
            case 'boolean':
                $value = boolval($value);
                break;
        }

        return $value;
    }

    /**
     * Validate the configuration data type
     * @param mixed $value
     * @param string $type
     * @return bool
     */
    public static function isValueValidForDataType(mixed $value, string $type): bool
    {
        if ($type === 'float' || $type === 'double') {
            return (bool) filter_var($value, FILTER_VALIDATE_FLOAT);
        }

        if ($type === 'integer') {
            return (bool) filter_var($value, FILTER_VALIDATE_INT);
        }

        if ($type === 'boolean') {
            return (bool) filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        return true;
    }
}
