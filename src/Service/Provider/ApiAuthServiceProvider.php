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
 *  @file ApiAuthServiceProvider.php
 *
 *  The Framework API authentication service provider class
 *
 *  @package    Platine\Framework\Service\Provider
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Service\Provider;

use Platine\Framework\Auth\Authentication\JWTAuthentication;
use Platine\Framework\Auth\AuthenticationInterface;
use Platine\Framework\Auth\Authorization\Cache\NullCacheStorage;
use Platine\Framework\Auth\Authorization\PermissionCacheInterface;
use Platine\Framework\Auth\Middleware\ApiAuthenticationMiddleware;
use Platine\Framework\Auth\Middleware\ApiAuthorizationMiddleware;
use Platine\Framework\Auth\Repository\TokenRepository;
use Platine\Framework\Security\JWT\Encoder\Base64UrlSafeEncoder;
use Platine\Framework\Security\JWT\EncoderInterface;
use Platine\Framework\Security\JWT\JWT;
use Platine\Framework\Security\JWT\Signer\HMAC;
use Platine\Framework\Security\JWT\SignerInterface;
use Platine\Framework\Service\ServiceProvider;

/**
 * @class ApiAuthServiceProvider
 * @package Platine\Framework\Service\Provider
 */
class ApiAuthServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $this->app->share(ApiAuthenticationMiddleware::class);
        $this->app->share(ApiAuthorizationMiddleware::class);
        $this->app->share(AuthenticationInterface::class, JWTAuthentication::class);
        $this->app->share(SignerInterface::class, HMAC::class);
        $this->app->share(EncoderInterface::class, Base64UrlSafeEncoder::class);
        $this->app->share(JWT::class);
        $this->app->bind(TokenRepository::class);
        $this->app->bind(PermissionCacheInterface::class, NullCacheStorage::class);
    }
}
