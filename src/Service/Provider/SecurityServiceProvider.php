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
 *  @file SecurityServiceProvider.php
 *
 *  The Framework security service provider class
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

use Platine\Config\Config;
use Platine\Container\ContainerInterface;
use Platine\Framework\Http\Middleware\CorsMiddleware;
use Platine\Framework\Http\Middleware\CsrfMiddleware;
use Platine\Framework\Http\Middleware\SecurityPolicyMiddleware;
use Platine\Framework\Security\Csrf\CsrfManager;
use Platine\Framework\Security\Csrf\CsrfStorageInterface;
use Platine\Framework\Security\Csrf\Storage\CsrfSessionStorage;
use Platine\Framework\Security\SecurityPolicy;
use Platine\Framework\Service\ServiceProvider;
use Platine\Route\Router;

/**
 * @class SecurityServiceProvider
 * @package Platine\Framework\Service\Provider
 */
class SecurityServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $this->app->bind(SecurityPolicy::class, function (ContainerInterface $app) {
            return new SecurityPolicy(
                $app->get(Config::class),
                $app->get(Router::class),
                $app->get(Config::class)->get('security.policies', [])
            );
        });
        $this->app->bind(CsrfStorageInterface::class, CsrfSessionStorage::class);
        $this->app->bind(CsrfManager::class);
        $this->app->bind(SecurityPolicyMiddleware::class);
        $this->app->bind(CorsMiddleware::class);
        $this->app->bind(CsrfMiddleware::class);
    }
}
