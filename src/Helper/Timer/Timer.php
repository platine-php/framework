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
 *  @file Timer.php
 *
 *  The Timer class
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
 * @class Timer
 * @package Platine\Framework\Helper\Timer
 */
class Timer
{
    public const NOT_STARTED = 0;
    public const STARTED = 1;
    public const PAUSED = 2;
    public const STOPPED = 3;

    /**
     * The name of timer
     * @var string
     */
    protected string $name;

    /**
     * The current timer state
     * @var int
     */
    protected int $state = self::NOT_STARTED;

    /**
     * The timer start time
     * @var float
     */
    protected float $startTime = 0;

    /**
     * The timer runtime
     * @var float
     */
    protected float $runtime = 0;

    /**
     * Create new timer instance
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Return the timer time
     * @return float
     */
    public function getTime(): float
    {
        $currentRuntime = 0;
        if ($this->state === self::STARTED) {
            $time = microtime(true);
            $currentRuntime = $time - $this->startTime;
        }

        return $this->runtime + $currentRuntime;
    }

    /**
     * Stop the timer
     * @return bool
     */
    public function stop(): bool
    {
        if ($this->state === self::STARTED) {
            $this->pause();
        }

        if ($this->state === self::PAUSED) {
            $this->state = self::STOPPED;

            return true;
        }

        return false;
    }

    /**
     * Pause the timer
     * @return bool
     */
    public function pause(): bool
    {
        if ($this->state !== self::STARTED) {
            return false;
        }
        $time = microtime(true);

        $this->runtime += ($time - $this->startTime);
        $this->startTime = 0;

        $this->setState(self::PAUSED);

        return true;
    }

    /**
     * Start the timer
     * @return bool
     */
    public function start(): bool
    {
        if ($this->canStart() === false) {
            return false;
        }

        $this->startTime = (float) microtime(true);

        $this->setState(self::STARTED);

        return true;
    }

    /**
     * Confirms if the timer can be started or not. A timer can be started
     * if it is in following states:
     *   - Not started and
     *   - paused
     * Timer can not be started if it is already running or stopped.
     * @return bool
     */
    public function canStart(): bool
    {
        if (in_array($this->state, [self::NOT_STARTED, self::PAUSED])) {
            return true;
        }

        return false;
    }

    /**
     * Return the timer name
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Return the current state
     * @return int
     */
    public function getState(): int
    {
        return $this->state;
    }


    /**
     * Set the timer state
     * @param int $state
     * @return $this
     */
    protected function setState(int $state): self
    {
        if ($state < 0 || $state > 3) {
            throw new InvalidArgumentException(sprintf(
                'Invalid state [%d] must be between [0-3]',
                $state
            ));
        }

        $this->state = $state;

        return $this;
    }
}
