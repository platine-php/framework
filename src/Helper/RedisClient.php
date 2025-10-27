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
 *  @file RedisClient.php
 *
 *  The lightweight Redis client class
 *
 *  @package    Platine\Framework\Helper
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Helper;

use RuntimeException;

/**
 * @class RedisClient
 * @package Platine\Framework\Helper
 *
 * @method string get(string $key)
 * @method string del(string $key)
 * @method string ttl(string $key)
 * @method string exists(string $key)
 * @method string persist(string $key)
 * @method string incr(string $key)
 * @method string decr(string $key)
 * @method string expire(string $key, int $seconds)
 * @method string incrby(string $key, int $increment)
 * @method string decrby(string $key, int $decrement)
 * @method string set(string $key, string $value)
 */
class RedisClient
{
    /**
     * The connection socket
     * line length not limited
     * @var resource|false
     */
    protected $socket = false;


    /**
     * Create new instance
     * @param string $host the Redis server address
     * @param int $port the Redis server port
     */
    public function __construct(
        protected string $host = 'localhost',
        protected int $port = 6379,
    ) {
    }

    /**
     * Dynamic method call based on Redis commands
     * @see https://redis.io/docs/latest/commands/
     * @param string $method
     * @param array<mixed> $args
     * @return mixed
     */
    public function __call(string $method, array $args): mixed
    {
        array_unshift($args, $method);
        $cmd = sprintf('*%d', count($args)) . "\r\n";
        foreach ($args as $item) {
            $cmd .= '$' . strlen($item) . "\r\n" . $item . "\r\n";
        }

        fwrite($this->getSocket(), $cmd);

        return $this->getResponse();
    }

    /**
     * Return the response from socket
     * @return array<string>|string|string
     * @throws RuntimeException
     */
    protected function getResponse(): array|string|null
    {
        $line = fgets($this->getSocket());
        if ($line === false) {
            throw new RuntimeException('Can not read from redis socket');
        }
        [$type, $result] = [$line[0], substr($line, 1, strlen($line) - 3)];
        if ($type === '-') {
            // Error
            throw new RuntimeException(sprintf('Redis socket return error: %s', $result));
        }

        if ($type === '$') { // bulk reply
            if ($result === '-1') {
                $result = null;
            } else {
                /** @var int<1, max> $length */
                $length = (int) $result + 2;
                $line = fread($this->getSocket(), $length);
                if ($line === false) {
                    throw new RuntimeException('Can not read from redis socket for bulk reply');
                }
                $result = substr($line, 0, strlen($line) - 2);
            }
        } elseif ($type === '*') { // multi-bulk reply
            $count = (int) $result;
            for ($i = 0 , $result = []; $i < $count; $i++) {
                $result[] = $this->getResponse();
            }
        }

        return $result;
    }

    /**
     * Return the socket instance
     * @return resource
     * @throws RuntimeException
     */
    protected function getSocket()
    {
        if (is_resource($this->socket)) {
            return $this->socket;
        }
        $errorCode = null;
        $errorMessage = null;
        $socket = stream_socket_client(
            sprintf('%s:%d', $this->host, $this->port),
            $errorCode,
            $errorMessage
        );
        if ($socket === false) {
            throw new RuntimeException(sprintf(
                'Can not connect to Redis [%d] -> [%s]',
                $errorCode,
                $errorMessage
            ));
        }

        return $this->socket = $socket;
    }
}
