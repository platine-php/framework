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
 * Copyright (c) 2015 PHP Reboot
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
 *  @file Watch.php
 *
 *  The Stop Watch class
 *
 *  @package    Platine\Framework\Helper\Timer
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Helper\Timer;

use InvalidArgumentException;

/**
 * @class Watch
 * @package Platine\Framework\Helper\Timer
 */
class Watch
{
    /**
     * The default name to use if none is provided
     */
    public const WATCH_DEFAULT_NAME = '__default__';

    /**
     * The timer list
     * @var array<string, Timer>
     */
    protected array $timers = [];

    /**
     * Start the timer
     * @param string $name
     * @return bool
     */
    public function start(string $name = self::WATCH_DEFAULT_NAME): bool
    {
        $this->addWatch($name);

        return $this->getWatch($name)->start();
    }

    /**
     * Pause the timer
     * @param string $name
     * @return bool
     */
    public function pause(string $name = self::WATCH_DEFAULT_NAME): bool
    {
        if ($this->exists($name) === false) {
            return false;
        }

        return $this->getWatch($name)->pause();
    }

    /**
     * Stop the timer
     * @param string $name
     * @return bool
     */
    public function stop(string $name = self::WATCH_DEFAULT_NAME): bool
    {
        if ($this->exists($name) === false) {
            return false;
        }

        return $this->getWatch($name)->stop();
    }

    /**
     * Get the timer time
     * @param string $name
     * @return float
     */
    public function getTime(string $name = self::WATCH_DEFAULT_NAME): float
    {
        if ($this->exists($name) === false) {
            return -1;
        }

        return $this->getWatch($name)->getTime();
    }

    /**
     * Add a new time to the stop watch.
     * @param string $name
     * @return $this
     */
    public function addWatch(string $name): self
    {
        if (array_key_exists($name, $this->timers)) {
            throw new InvalidArgumentException(sprintf(
                'Watch with the name [%s] already exist',
                $name
            ));
        }

        $timer = new Timer($name);
        $this->timers[$name] = $timer;

        return $this;
    }

    /**
    * Get watch
    * @param string $name
    * @return Timer
    */
    public function getWatch(string $name): Timer
    {
        if (array_key_exists($name, $this->timers) === false) {
            throw new InvalidArgumentException(sprintf(
                'Watch with the name [%s] does not exist',
                $name
            ));
        }

        return $this->timers[$name];
    }

    /**
     * Whether the timer with given name exists
     * @param string $name
     * @return bool
     */
    public function exists(string $name): bool
    {
        return array_key_exists($name, $this->timers);
    }

    /**
     * Return the total timers
     * @return int
     */
    public function count(): int
    {
        return count($this->timers);
    }

    /**
     * Return watch details
     * @return array<string, float>
     */
    public function info(): array
    {
        $result = [];
        foreach ($this->timers as $name => /** @var Timer $timer */ $timer) {
            $result[$name] = $timer->getTime();
        }
        return $result;
    }
}
