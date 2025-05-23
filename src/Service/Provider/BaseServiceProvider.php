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
 *  @file BaseServiceProvider.php
 *
 *  The Framework base service provider class
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
use Platine\Console\Application as ConsoleApp;
use Platine\Container\ContainerInterface;
use Platine\Container\Resolver\ConstructorResolver;
use Platine\Container\Resolver\ResolverInterface;
use Platine\Framework\App\Application;
use Platine\Framework\Http\Emitter\EmitterInterface;
use Platine\Framework\Http\Emitter\ResponseEmitter;
use Platine\Framework\Http\Maintenance\Driver\FileMaintenanceDriver;
use Platine\Framework\Http\Maintenance\MaintenanceDriverInterface;
use Platine\Framework\Http\Middleware\MaintenanceMiddleware;
use Platine\Framework\Service\ServiceProvider;
use Platine\Http\Handler\MiddlewareResolver;
use Platine\Http\Handler\MiddlewareResolverInterface;
use Platine\Route\RouteCollection;
use Platine\Route\RouteCollectionInterface;
use Platine\Route\Router;


/**
 * @class BaseServiceProvider
 * @package Platine\Framework\Service\Provider
 */
class BaseServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $this->app->share(Application::class, $this->app);
        $this->app->share('app', $this->app);
        $this->app->share(ConsoleApp::class, function () {
            return new ConsoleApp('PLATINE CONSOLE', '1.0.0');
        });
        $this->app->share(ContainerInterface::class, $this->app);
        $this->app->bind(ResolverInterface::class, ConstructorResolver::class);
        $this->app->share(Router::class);
        $this->app->bind(RouteCollectionInterface::class, RouteCollection::class);
        $this->app->bind(MiddlewareResolverInterface::class, MiddlewareResolver::class);
        $this->app->bind(MaintenanceMiddleware::class);
        $this->app->bind(MaintenanceDriverInterface::class, FileMaintenanceDriver::class);
        $this->app->bind(EmitterInterface::class, function (ContainerInterface $app) {
            return new ResponseEmitter(
                $app->get(Config::class)->get('app.response_chunck_size', null)
            );
        });
    }
}
