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
 *  @file UserServiceProvider.php
 *
 *  User service provider class
 *
 *  @package    Platine\Framework\Demo\Provider
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Demo\Provider;

use Platine\Framework\Demo\Action\User\BatchAction;
use Platine\Framework\Demo\Action\User\CreateAction;
use Platine\Framework\Demo\Action\User\DetailAction;
use Platine\Framework\Demo\Action\User\EditAction;
use Platine\Framework\Demo\Action\User\ListAction;
use Platine\Framework\Demo\Action\User\LoginAction;
use Platine\Framework\Demo\Action\User\LogoutAction;
use Platine\Framework\Service\ServiceProvider;
use Platine\Route\Router;

/**
 * @class UserServiceProvider
 * @package Platine\Framework
 */
class UserServiceProvider extends ServiceProvider
{

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $this->app->bind(BatchAction::class);
        $this->app->bind(EditAction::class);
        $this->app->bind(CreateAction::class);
        $this->app->bind(LoginAction::class);
        $this->app->bind(ListAction::class);
        $this->app->bind(LogoutAction::class);
        $this->app->bind(DetailAction::class);
    }

    /**
     * {@inheritdoc}
     */
    public function addRoutes(Router $router): void
    {
        $router->group('/users', function (Router $router) {
            $router->get('', ListAction::class, 'user_list', ['permission' => 'users']);
            $router->get('/detail/{id:i}', DetailAction::class, 'user_detail');
            $router->post('/batch', BatchAction::class, 'user_batch');
            $router->get('/logout', LogoutAction::class, 'user_logout');
            $router->add('/login', LoginAction::class, ['GET', 'POST'], 'user_login');
            $router->add('/add', CreateAction::class, ['GET', 'POST'], 'user_create');
            $router->add('/edit/{id:i}', EditAction::class, ['GET', 'POST'], 'user_edit');
        });
    }
}
