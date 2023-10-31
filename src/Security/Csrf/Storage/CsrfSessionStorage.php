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
 *  @file CsrfSessionStorage.php
 *
 *  The CSRF storage based on session
 *
 *  @package    Platine\Framework\Security\Csrf\Storage
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Security\Csrf\Storage;

use Platine\Framework\Security\Csrf\CsrfStorageInterface;
use Platine\Session\Session;

/**
 * @class CsrfSessionStorage
 * @package Platine\Framework\Security\Csrf\Storage
 */
class CsrfSessionStorage implements CsrfStorageInterface
{
    /**
     * The session key used to store CSRF data
     */
    public const CSRF_SESSION_KEY = 'pl_csrf_data';


    /**
     * The session instance
     * @var Session
     */
    protected Session $session;

    /**
     * Create new instance
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }


    /**
     * {@inheritdoc}
     */
    public function get(string $name): ?string
    {
        $key = $this->getKeyName($name);
        $data = $this->session->get($key, []);

        if (count($data) === 0 || $data['expire'] <= time()) {
            return null;
        }

        return $data['value'];
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $name, string $token, int $expire): void
    {
        $key = $this->getKeyName($name);
        $this->session->set($key, [
            'value' => $token,
            'expire' => $expire,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $name): void
    {
        $key = $this->getKeyName($name);
        $this->session->remove($key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): void
    {
        $this->session->remove(self::CSRF_SESSION_KEY);
    }

    /**
     * Return the session key
     * @param string $name
     * @return string
     */
    private function getKeyName(string $name): string
    {
        return sprintf('%s.%s', self::CSRF_SESSION_KEY, $name);
    }
}
