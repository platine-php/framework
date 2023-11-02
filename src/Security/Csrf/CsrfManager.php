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
 *  @file CsrfManager.php
 *
 *  The CSRF manager class
 *
 *  @package    Platine\Framework\Security\Csrf
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Security\Csrf;

use Platine\Config\Config;
use Platine\Framework\Http\RequestData;
use Platine\Framework\Security\Csrf\CsrfStorageInterface;
use Platine\Framework\Security\Csrf\Storage\CsrfNullStorage;
use Platine\Http\ServerRequestInterface;
use Platine\Stdlib\Helper\Str;

/**
 * @class CsrfManager
 * @package Platine\Framework\Security\Csrf
 */
class CsrfManager
{
    /**
     * The application configuration class
     * @var Config
     */
    protected Config $config;

    /**
     * The storage to be used
     * @var CsrfStorageInterface
     */
    protected CsrfStorageInterface $storage;

    /**
     * Create new instance
     * @param Config $config
     * @param CsrfStorageInterface|null $storage
     */
    public function __construct(
        Config $config,
        ?CsrfStorageInterface $storage = null
    ) {
        $this->config = $config;
        $this->storage = $storage ??  new CsrfNullStorage();
    }

    /**
     * Validate the token
     * @param ServerRequestInterface $request
     * @param string|null $key
     * @return bool
     */
    public function validate(ServerRequestInterface $request, ?string $key = null): bool
    {
        if ($key === null) {
            $key = $this->getConfigValue('key');
        }

        $storageToken = $this->storage->get($key);
        if ($storageToken === null) {
            return false;
        }

        $token = $this->getRequestToken($request, $key);

        if ($token === null || $token !== $storageToken) {
            return false;
        }

        return true;
    }

    /**
     * Return the token
     * @param string|null $key
     * @return string
     */
    public function getToken(?string $key = null): string
    {
        if ($key === null) {
            $key = $this->getConfigValue('key');
        }

        $value = $this->storage->get($key);
        if ($value === null) {
            // Generate the token
            $value = sha1(Str::randomToken(24));
            $expire = $this->getConfigValue('expire') ?? 300;
            $expireTime = time() + $expire;

            $this->storage->set($key, $value, $expireTime);
        }

        return $value;
    }

    /**
     * Return the token query to be used in query string
     * @param string|null $key
     * @return array<string, string>
     */
    public function getTokenQuery(?string $key = null): array
    {
        $token = $this->getToken($key);

        if ($key === null) {
            $key = $this->getConfigValue('key');
        }

        return [$key => $token];
    }

    /**
     * Clear all CSRF data from storage
     * @return void
     */
    public function clear(): void
    {
        $this->storage->clear();
    }

    /**
     * Return the token from request
     * @param ServerRequestInterface $request
     * @param string $key
     * @return string|null
     */
    protected function getRequestToken(ServerRequestInterface $request, string $key): ?string
    {
        $param = new RequestData($request);
        $token = $param->post($key);
        if ($token === null) {
            $token = $param->get($key);
        }

        if ($token === null) {
            $token = $request->getHeaderLine('X-Csrf-Token');
        }


        return $token;
    }

    /**
     * Return the CSRF configuration value
     * @param string $key
     * @return mixed
     */
    private function getConfigValue(string $key)
    {
        $config = $this->config->get('security.csrf', []);

        return $config[$key] ?? null;
    }
}
