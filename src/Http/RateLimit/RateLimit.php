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
 *  @file RateLimit.php
 *
 *  The Rate Limit base class
 *
 *  @package    Platine\Framework\Http\RateLimit
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Http\RateLimit;

use Platine\Framework\Http\RateLimit\Exception\LimitExceededException;


/**
 * @class RateLimit
 * @package Platine\Framework\Http\RateLimit
 */
class RateLimit
{
    /**
     * Create new instance
     * @param RateLimitStorageInterface $storage
     * @param Rate $rate
     * @param string $name
     */
    public function __construct(
        protected RateLimitStorageInterface $storage,
        protected Rate $rate,
        protected string $name = 'api'
    ) {
    }

    /**
     * Rate Limiting
     * @param string $id
     * @param float $used
     * @return void
     */
    public function limit(string $id, float $used = 1.0): void
    {
        $rate = $this->rate->getQuota() / $this->rate->getInterval();
        $allowKey = $this->getAllowKey($id);
        $timeKey = $this->getTimeKey($id);

        if ($this->storage->exists($timeKey) === false) {
            // first hit; setup storage; allow.
            $this->storage->set($timeKey, time(), $this->rate->getInterval());
            $this->storage->set(
                $allowKey,
                ($this->rate->getQuota() - $used),
                $this->rate->getInterval()
            );

            return;
        }

        $currentTime = time();
        $timePassed = $currentTime - $this->storage->get($timeKey);
        $this->storage->set($timeKey, $currentTime, $this->rate->getInterval());

        $quota = $this->storage->get($allowKey);
        $quota += $timePassed * $rate;

        if ($quota > $this->rate->getQuota()) {
            $quota = $this->rate->getQuota(); // throttle
        }

        if ($quota < $used) {
            // need to wait for more 'tokens' to be in the bucket.
            $this->storage->set($allowKey, $quota, $this->rate->getInterval());

            throw LimitExceededException::create($id, $this->rate);
        }

        $this->storage->set($allowKey, $quota - $used, $this->rate->getInterval());
    }

    /**
     * Return the remaining quota to be used
     * @param string $id
     * @return int the number of operation that can be made before hitting a limit.
     */
    public function getRemainingAttempts(string $id): int
    {
        $this->limit($id, 0.0);

        $allowKey = $this->getAllowKey($id);

        if ($this->storage->exists($allowKey) === false) {
            return $this->rate->getQuota();
        }

        return (int) max(0, floor($this->storage->get($allowKey)));
    }

    /**
     * Purge rate limit record for the given key
     * @param string $id
     * @return void
     */
    public function purge(string $id): void
    {
        $this->storage->delete($this->getAllowKey($id));
        $this->storage->delete($this->getTimeKey($id));
    }

    /**
     * Return the storage key used for time of the given identifier
     * @param string $id
     * @return string
     */
    protected function getTimeKey(string $id): string
    {
        return sprintf(
            '%s:%s:time',
            $this->name,
            $id
        );
    }

    /**
     * Return the storage key used for allow of the given identifier
     * @param string $id
     * @return string
     */
    protected function getAllowKey(string $id): string
    {
        return sprintf(
            '%s:%s:allow',
            $this->name,
            $id
        );
    }
}
