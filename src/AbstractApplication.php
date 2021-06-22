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
 *  @file AbstractApplication.php
 *
 *  The Platine Base Application class
 *
 *  @package    Platine\Framework
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework;

use Platine\Config\Config;
use Platine\Config\FileLoader;
use Platine\Config\LoaderInterface;
use Platine\Container\ConstructorResolver;
use Platine\Container\Container;
use Platine\Container\ContainerInterface;
use Platine\Container\ResolverInterface;
use Platine\Event\Dispatcher;
use Platine\Event\DispatcherInterface;
use Platine\Framework\Http\Emitter\EmitterInterface;
use Platine\Framework\Http\Emitter\ResponseEmitter;
use Platine\Http\Handler\CallableResolver;
use Platine\Http\Handler\CallableResolverInterface;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Logger\Configuration;
use Platine\Logger\Logger;
use Platine\Logger\LoggerInterface;

/**
 * class AbstractApplication
 * @package Platine\Framework
 */
abstract class AbstractApplication extends Container
{

    /**
     * The application version
     */
    public const VERSION = '1.0.0-dev';

    /**
     * The base path for this application
     * @var string
     */
    protected string $basePath;

    /**
     * The configuration instance
     * @var Config
     */
    protected Config $config;

    /**
     * The logger instance to use
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * Create new instance
     * @param string|null $basePath
     */
    public function __construct(?string $basePath = null)
    {
        parent::__construct();
        $this->addCoreServiceProviders();

        $this->config = $this->get(Config::class);
        $this->logger = $this->get(LoggerInterface::class);

        $this->basePath = $basePath ?? $this->config->get('app.base_path', '/');
    }

    /**
     * Execute the application
     * @return void
     */
    abstract public function execute(): void;

    /**
     * Load core service provider
     * @return void
     */
    protected function addCoreServiceProviders(): void
    {
        $this->bind(LoaderInterface::class, FileLoader::class, [
            'path' => __DIR__ . '/../../../packages/app/config'
        ]);
        $this->share(Config::class);
        $this->bind(ContainerInterface::class, $this);
        $this->bind(ResolverInterface::class, ConstructorResolver::class);
        $this->bind(CallableResolverInterface::class, CallableResolver::class);
        $this->bind(RequestHandlerInterface::class, $this);
        $this->bind(EmitterInterface::class, function (ContainerInterface $app) {
            return new ResponseEmitter(
                $app->get(Config::class)->get('app.response_length', null)
            );
        });

        $this->bind(LoggerInterface::class, function (ContainerInterface $app) {
            return new Logger(
                new Configuration($app->get(Config::class)->get('logging', []))
            );
        });
        $this->bind(DispatcherInterface::class, Dispatcher::class);
    }
}
