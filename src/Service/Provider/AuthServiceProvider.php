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
 *  @file AuthServiceProvider.php
 *
 *  The Framework authentication service provider class
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

use Platine\Framework\Auth\Authentication\SessionAuthentication;
use Platine\Framework\Auth\AuthenticationInterface;
use Platine\Framework\Auth\Authorization\SessionAuthorization;
use Platine\Framework\Auth\AuthorizationInterface;
use Platine\Framework\Auth\Middleware\AuthenticationMiddleware;
use Platine\Framework\Auth\Middleware\AuthorizationMiddleware;
use Platine\Framework\Auth\Repository\PermissionRepository;
use Platine\Framework\Auth\Repository\RoleRepository;
use Platine\Framework\Auth\Repository\UserRepository;
use Platine\Framework\Console\PasswordGenerateCommand;
use Platine\Framework\Service\ServiceProvider;
use Platine\Security\Hash\BcryptHash;
use Platine\Security\Hash\HashInterface;

/**
 * @class AuthServiceProvider
 * @package Platine\Framework\Service\Provider
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        //Repositories
        $this->app->bind(RoleRepository::class);
        $this->app->bind(PermissionRepository::class);
        $this->app->bind(UserRepository::class);

        //Authentication
        $this->app->bind(AuthenticationMiddleware::class);
        $this->app->bind(AuthenticationInterface::class, SessionAuthentication::class);

        //Authorization
        $this->app->bind(AuthorizationMiddleware::class);
        $this->app->bind(AuthorizationInterface::class, SessionAuthorization::class);

        //Hash
        $this->app->bind(HashInterface::class, BcryptHash::class);

        // Command
        $this->app->bind(PasswordGenerateCommand::class);

        $this->addCommand(PasswordGenerateCommand::class);
    }
}
