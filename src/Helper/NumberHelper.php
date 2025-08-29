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

declare(strict_types=1);

namespace Platine\Framework\Helper;

/**
 * @class NumberHelper
 * @package Platine\Framework\Helper
 */
class NumberHelper
{
    /**
     * Float comparison delta value
     */
    public const FLOAT_DELTA = 0.004;

    /**
     * Float can't be compared using equality this function is useful
     * to compare two float value using epsilon
     * @param float $a
     * @param float $b
     * @return bool
     */
    public static function isEqual(float $a, float $b): bool
    {
        return abs($a - $b) < self::FLOAT_DELTA;
    }

    /**
     * If two float is not equal
     * @param float $a
     * @param float $b
     * @return bool
     */
    public static function isNotEqual(float $a, float $b): bool
    {
        return self::isEqual($a, $b) === false;
    }

    /**
     * If first parameter is less than the second one
     * @param float $a
     * @param float $b
     * @return bool
     */
    public static function isLessThan(float $a, float $b): bool
    {
        return ($b - $a) > self::FLOAT_DELTA;
    }

    /**
     * If first parameter is greater than the second one
     * @param float $a
     * @param float $b
     * @return bool
     */
    public static function isGreaterThan(float $a, float $b): bool
    {
        return ($a - $b) > self::FLOAT_DELTA;
    }

    /**
     * Return the given float number to string
     * in order to fix some issue in some OS "." is replaced by ","
     * @param float|int $amount
     * @return string
     */
    public static function numberToString(float|int $amount): string
    {
        $value = (string) $amount;
        if (stripos($value, 'e') !== false) {
            // PHP use scientific notation if decimal has 4 zeros
            // after dot. so use number format instead of
            $value = self::floatToString($amount);
        }

        return str_replace(',', '.', $value);
    }

    /**
     * Convert float number to string without scientific notation
     * @param float $amount
     * @return string
     */
    public static function floatToString(float $amount): string
    {
        $value = (string) $amount;
        if (strpos($value, 'e') === false && strpos($value, 'E') === false) {
            return $value;
        }
        list($base, $decimal) = explode('E', str_replace('e', 'E', $value));
        $exponent = (int)$decimal;
        if ($exponent >= 0) {
            // Positive exponent: add zeros to the right
            $parts = explode('.', $base);
            $integerPart = $parts[0];
            $decimalPart = $parts[1] ?? '';

            $numToMove = min($exponent, strlen($decimalPart));
            $integerPart .= substr($decimalPart, 0, $numToMove);
            $newDecimalPart = substr($decimalPart, $numToMove);

            $integerPart .= str_repeat('0', $exponent - $numToMove);

            return $newDecimalPart === '' ? $integerPart : sprintf(
                '%s.%s',
                $integerPart,
                $newDecimalPart
            );
        }
        // Negative exponent: add zeros to the left
        $absExponent = abs($exponent);
        $parts = explode('.', $base);
        $integerPart = $parts[0];
        $decimalPart = $parts[1] ?? '';

        $fullNumber = $integerPart . $decimalPart;
        $numZeros = $absExponent - strlen($integerPart);
        // @codeCoverageIgnoreStart
        if ($numZeros < 0) { // Exponent is smaller than integer part length
            $insertPoint = strlen($integerPart) + $exponent; // Exponent is negative
            return sprintf(
                '%s.%s',
                substr($fullNumber, 0, $insertPoint),
                substr($fullNumber, $insertPoint)
            );
        }
        // @codeCoverageIgnoreEnd

        return sprintf(
            '0.%s%s',
            str_repeat('0', $numZeros),
            $fullNumber
        );
    }
}
