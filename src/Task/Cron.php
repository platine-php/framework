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
 *  @file Cron.php
 *
 *  The Cron expression parser class
 *
 *  @package    Platine\Framework\Task
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Task;

use InvalidArgumentException;

/**
 * @class Cron
 * @package Platine\Framework\Task
 */
class Cron
{
    /**
     * Parse the given cron expression and finds next execution time(stamp)
     * @param string $expression
     *      0     1    2    3    4
     *      *     *    *    *    *
     *      -     -    -    -    -
     *      |     |    |    |    |
     *      |     |    |    |    +----- day of week (0 - 6) (Sunday=0)
     *      |     |    |    +------- month (1 - 12)
     *      |     |    +--------- day of month (1 - 31)
     *      |     +----------- hour (0 - 23)
     *      +------------- min (0 - 59)
     *
     * @param int $timestamp the after timestamp [default=current timestamp]
     * @return int the Unix timestamp
     */
    public static function parse(string $expression, ?int $timestamp = null): int
    {
        $cronExpression = trim($expression);

        $cronRegex = '/^((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+'
                . '((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+'
                . '((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+'
                . '((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+'
                . '((\*(\/[0-9]+)?)|[0-9\-\,\/]+)$/i';
        if (!preg_match($cronRegex, $cronExpression)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid cron expression [%s]',
                $expression
            ));
        }

        $crons = preg_split('/[\s]+/i', $cronExpression);
        $start = time();
        if ($timestamp !== null) {
            $start = $timestamp;
        }

        $dates = [
            'minutes' => self::parseNumber($crons[0], 0, 59),
            'hours' => self::parseNumber($crons[1], 0, 23),
            'dom' => self::parseNumber($crons[2], 1, 31),
            'month' => self::parseNumber($crons[3], 1, 12),
            'dow' => self::parseNumber($crons[4], 0, 6),
        ];

        // limited to time()+366 - no need
        // to check more than 1 year ahead
        $total = 60 * 60 * 24 * 366;
        for ($i = 0; $i <= $total; $i += 60) {
            $current = $start + $i;
            if (
                in_array((int) date('j', $current), $dates['dom']) &&
                in_array((int) date('n', $current), $dates['month']) &&
                in_array((int) date('w', $current), $dates['dow']) &&
                in_array((int) date('G', $current), $dates['hours']) &&
                in_array((int) date('i', $current), $dates['minutes'])
            ) {
                return $current;
            }
        }

        return $start;
    }

    /**
     * Parse and return a single cron style notation
     * into numeric value
     * @param string $expression
     * @param int $min minimum possible value
     * @param int $max maximum possible value
     * @return array<int>
     */
    protected static function parseNumber(string $expression, int $min, int $max): array
    {
        $result = [];
        $values = explode(',', $expression);
        foreach ($values as $value) {
            $slashValues = explode('/', $value);
            $step = $slashValues[1] ?? 1;
            $minusValues = explode('-', $slashValues[0]);
            $minimum = count($minusValues) === 2 ? $minusValues[0]
                        : ($slashValues[0] === '*' ? $min : $slashValues[0]);

            $maximum = count($minusValues) === 2 ? $minusValues[1]
                        : ($slashValues[0] === '*' ? $max : $slashValues[0]);

            for ($i = $minimum; $i <= $maximum; $i += $step) {
                $result[$i] = intval($i);
            }
        }
        ksort($result);

        return $result;
    }
}
