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
 *  @package    Platine\Framework\App
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\App;

use Platine\Config\Config;
use Platine\Container\Container;
use Platine\Framework\Service\Provider\BaseServiceProvider;
use Platine\Framework\Service\Provider\EventServiceProvider;
use Platine\Framework\Service\Provider\LoggerServiceProvider;
use Platine\Framework\Service\Provider\RoutingServiceProvider;
use Platine\Framework\Service\ServiceProvider;
use Platine\Logger\LoggerInterface;

/**
 * class AbstractApplication
 * @package Platine\Framework\App
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
     * The list of service providers
     * @var array<string, ServiceProvider>
     */
    protected array $providers = [];

    /**
     * Whether the system already booted
     * @var bool
     */
    protected bool $booted = false;

    /**
     * The application environment
     * @var string
     */
    protected string $env = 'dev';

    /**
     * Create new instance
     * @param string|null $basePath
     */
    public function __construct(?string $basePath = null)
    {
        parent::__construct();
        $this->loadCoreServiceProviders();
        $this->loadConfiguredServiceProviders();

        $this->boot();

        $this->config = $this->get(Config::class);
        $this->logger = $this->get(LoggerInterface::class);

        $this->basePath = $basePath ?? $this->config->get('app.base_path', '/');
    }

    /**
     * Return the application version
     * @return string
     */
    public function version(): string
    {
        return self::VERSION;
    }

    /**
     * Boot the application
     * @return void
     */
    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        foreach ($this->providers as $provider) {
            $this->bootServiceProvider($provider);
        }

        $this->booted = true;
    }

    /**
     * Register the service provider
     * @param string|ServiceProvider $provider
     * @param bool $force whether to force registration of provider
     * if already loaded
     * @return ServiceProvider
     */
    public function registerServiceProvider(
        $provider,
        bool $force = false
    ): ServiceProvider {
        $registered = $this->getServiceProvider($provider);
        if ($registered && !$force) {
            return $registered;
        }

        if (is_string($provider)) {
            $provider = $this->createServiceProvider($provider);
        }

        $provider->register();

        $this->markProviderAsRegistered($provider);

        if ($this->booted) {
            $this->bootServiceProvider($provider);
        }

        return $provider;
    }

    /**
     * Return the registered service provider if exist
     * @param string|ServiceProvider $provider
     * @return ServiceProvider|null
     */
    public function getServiceProvider($provider): ?ServiceProvider
    {
        $name = is_string($provider)
                ? $provider
                : get_class($provider);

        return $this->providers[$name] ?? null;
    }

    /**
     * Execute the application
     * @return void
     */
    abstract public function execute(): void;

    /**
     * Create service provider
     * @param string $provider
     * @return ServiceProvider
     */
    protected function createServiceProvider(string $provider): ServiceProvider
    {
        return new $provider($this);
    }

    /**
     * Boot the given service provider
     * @param ServiceProvider $provider
     * @return void
     */
    protected function bootServiceProvider(ServiceProvider $provider): void
    {
        $provider->boot();
    }

    /**
     * Set the given service provider as registered
     * @param ServiceProvider $provider
     * @return void
     */
    protected function markProviderAsRegistered(ServiceProvider $provider): void
    {
        $this->providers[get_class($provider)] = $provider;
    }

    /**
     * Load framework core service providers
     * @return void
     */
    protected function loadCoreServiceProviders(): void
    {
        $this->registerServiceProvider(new BaseServiceProvider($this));
        $this->registerServiceProvider(new LoggerServiceProvider($this));
        $this->registerServiceProvider(new EventServiceProvider($this));
        $this->registerServiceProvider(new RoutingServiceProvider($this));
    }

    /**
     * Load configured service providers
     * @return void
     */
    protected function loadConfiguredServiceProviders(): void
    {
        /** @var Config $config */
        $config = $this->get(Config::class);

        /** @var string[] $providers */
        $providers = $config->get('providers', []);
        foreach ($providers as $provider) {
            $this->registerServiceProvider($provider);
        }
    }
}
