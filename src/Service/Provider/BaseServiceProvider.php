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
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Service\Provider;

use Platine\Config\Config;
use Platine\Config\FileLoader;
use Platine\Config\LoaderInterface;
use Platine\Container\ConstructorResolver;
use Platine\Container\ContainerInterface;
use Platine\Container\ResolverInterface;
use Platine\Framework\Http\Emitter\EmitterInterface;
use Platine\Framework\Http\Emitter\ResponseEmitter;
use Platine\Framework\Service\ServiceProvider;
use Platine\Http\Handler\CallableResolver;
use Platine\Http\Handler\CallableResolverInterface;
use Platine\Http\Handler\RequestHandlerInterface;

/**
 * class BaseServiceProvider
 * @package Platine\Framework\Service\Provider
 */
class BaseServiceProvider extends ServiceProvider
{

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $this->app->bind(LoaderInterface::class, FileLoader::class, [
            'path' => __DIR__ . '/../../../../../packages/app/config'
        ]);
        $this->app->share(Config::class);
        $this->app->bind('app', $this->app);
        $this->app->bind(ContainerInterface::class, $this->app);
        $this->app->bind(ResolverInterface::class, ConstructorResolver::class);
        $this->app->bind(CallableResolverInterface::class, CallableResolver::class);
        $this->app->bind(RequestHandlerInterface::class, $this->app);
        $this->app->bind(EmitterInterface::class, function (ContainerInterface $app) {
            return new ResponseEmitter(
                $app->get(Config::class)->get('app.response_length', null)
            );
        });
    }
}
