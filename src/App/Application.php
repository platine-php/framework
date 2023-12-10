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
 *  @file Application.php
 *
 *  The Platine Application class
 *
 *  @package    Platine\Framework\App
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\App;

use InvalidArgumentException;
use Platine\Config\Config;
use Platine\Config\FileLoader;
use Platine\Container\Container;
use Platine\Event\DispatcherInterface;
use Platine\Event\EventInterface;
use Platine\Event\ListenerInterface;
use Platine\Event\SubscriberInterface;
use Platine\Framework\Env\Loader;
use Platine\Framework\Helper\Timer\Watch;
use Platine\Framework\Http\Maintenance\MaintenanceDriverInterface;
use Platine\Framework\Service\Provider\BaseServiceProvider;
use Platine\Framework\Service\Provider\EventServiceProvider;
use Platine\Framework\Service\ServiceProvider;
use Platine\Stdlib\Helper\Path;

/**
 * @class Application
 * @package Platine\Framework\App
 */
class Application extends Container
{
    /**
     * The application version
     */
    public const VERSION = '1.0.0-dev';

    /**
     * The event dispatcher instance
     * @var DispatcherInterface
     */
    protected DispatcherInterface $dispatcher;

    /**
     * The stop watch instance
     * @var Watch
     */
    protected Watch $watch;

    /**
     * The base path for this application
     * @var string
     */
    protected string $basePath = '';

    /**
     * The vendor path
     * @var string
     */
    protected string $vendorPath = '';

    /**
     * The Application path
     * @var string
     */
    protected string $appPath = '';

    /**
     * The application root path
     * @var string
     */
    protected string $rootPath = '';

    /**
     * The application configuration path
     * @var string
     */
    protected string $configPath = 'config';

    /**
     * The application storage path
     * @var string
     */
    protected string $storagePath = 'storage';

    /**
     * The custom app name space
     * @var string
     */
    protected string $namespace = '';

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
     * The environment file name
     * @var string
     */
    protected string $environmentFile = '.env';

    /**
     * Create new instance
     * @param string $basePath
     */
    public function __construct(string $basePath = '')
    {
        parent::__construct();

        $this->basePath = $basePath;
        $this->watch = new Watch();
        $this->loadCoreServiceProviders();

        $this->dispatcher = $this->get(DispatcherInterface::class);
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
     * Return the environment file
     * @return string
     */
    public function getEnvironmentFile(): string
    {
        return $this->environmentFile;
    }


    /**
     * Set the environment file
     * @param string $environmentFile
     * @return $this
     */
    public function setEnvironmentFile(string $environmentFile): self
    {
        $this->environmentFile = $environmentFile;

        return $this;
    }

    /**
     * Return the root path
     * @return string
     */
    public function getRootPath(): string
    {
        return $this->rootPath;
    }

    /**
     * Set root path
     * @param string $rootPath
     * @return $this
     */
    public function setRootPath(string $rootPath): self
    {
        $this->rootPath = $rootPath;

        return $this;
    }

    /**
     * Return the application name space
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * Set the application name space
     * @param string $namespace
     * @return $this
     */
    public function setNamespace(string $namespace): self
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * Return the current environment
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->env;
    }

    /**
     * Set the environment
     * @param string $env
     * @return $this
     */
    public function setEnvironment(string $env)
    {
        $this->env = $env;

        return $this;
    }

    /**
     * Return the storage path
     * @return string
     */
    public function getStoragePath(): string
    {
        return $this->storagePath;
    }

    /**
     * Set the storage path
     * @param string $storagePath
     * @return $this
     */
    public function setStoragePath(string $storagePath): self
    {
        $this->storagePath = $storagePath;

        return $this;
    }


    /**
     * Return the vendor path
     * @return string
     */
    public function getVendorPath(): string
    {
        return $this->vendorPath;
    }

    /**
     * Set vendor path
     * @param string $vendorPath
     * @return $this
     */
    public function setVendorPath(string $vendorPath): self
    {
        $this->vendorPath = $vendorPath;

        return $this;
    }

     /**
     * Return the application root path
     * @return string
     */
    public function getAppPath(): string
    {
        return $this->appPath;
    }

    /**
     * Set Application path
     * @param string $appPath
     * @return $this
     */
    public function setAppPath(string $appPath): self
    {
        $this->appPath = $appPath;

        return $this;
    }

    /**
     * Return the application base path
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * Set the application base path
     * @param string $basePath
     * @return $this
     */
    public function setBasePath(string $basePath): self
    {
        $this->basePath = $basePath;

        return $this;
    }

    /**
     * Return the application configuration path
     * @return string
     */
    public function getConfigPath(): string
    {
        return $this->configPath;
    }

    /**
     * Set the application configuration path
     * @param string $configPath
     * @return $this
     */
    public function setConfigPath(string $configPath): self
    {
        $this->configPath = $configPath;

        return $this;
    }

    /**
     * Return the watch instance
     * @return Watch
     */
    public function watch(): Watch
    {
        return $this->watch;
    }

    /**
     * Return the current maintenance driver
     * @return MaintenanceDriverInterface
     */
    public function maintenance(): MaintenanceDriverInterface
    {
        return $this->get(MaintenanceDriverInterface::class);
    }

    /**
     * Whether the application is in maintenance
     * @return bool
     */
    public function isInMaintenance(): bool
    {
        return $this->maintenance()->active();
    }

    /**
     * Dispatches an event to all registered listeners.
     * @param  string|EventInterface $eventName the name of event
     * of instance of EventInterface
     * @param  EventInterface|null $event  the instance of EventInterface or null
     * @return EventInterface
     */
    public function dispatch($eventName, EventInterface $event = null): EventInterface
    {
        return $this->dispatcher->dispatch($eventName, $event);
    }

    /**
     * Register a listener for the given event.
     *
     * @param string $eventName the name of event
     * @param ListenerInterface|callable|string $listener the Listener
     * interface or any callable
     * @param int $priority the listener execution priority
     * @return $this
     */
    public function listen(
        string $eventName,
        $listener,
        int $priority = DispatcherInterface::PRIORITY_DEFAULT
    ): self {
        if (is_string($listener)) {
            $listener = $this->createListener($listener);
        }
        $this->dispatcher->addListener($eventName, $listener, $priority);

        return $this;
    }

    /**
     * Add event subscriber
     * @param SubscriberInterface $subscriber
     * @return $this
     */
    public function subscribe(SubscriberInterface $subscriber): self
    {
        $this->dispatcher->addSubscriber($subscriber);

        return $this;
    }

    /**
     * Return the list of providers
     * @return array<string, ServiceProvider>
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    /**
     * Return the list of service providers commands
     * @return array<class-string>
     */
    public function getProvidersCommands(): array
    {
        $commands = [];
        foreach ($this->providers as /** @var ServiceProvider $provider */ $provider) {
            $commands = array_merge($commands, $provider->getCommands());
        }

        return $commands;
    }

    /**
     * Return the list of service providers tasks
     * @return array<class-string>
     */
    public function getProvidersTasks(): array
    {
        $tasks = [];
        foreach ($this->providers as /** @var ServiceProvider $provider */ $provider) {
            $tasks = array_merge($tasks, $provider->getTasks());
        }

        return $tasks;
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
     * @param class-string<ServiceProvider>|ServiceProvider $provider
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
     * Load configured service providers
     * @return void
     */
    public function registerConfiguredServiceProviders(): void
    {
        /** @template T @var Config<T> $config */
        $config = $this->get(Config::class);

        /** @var class-string<ServiceProvider>[] $providers */
        $providers = $config->get('providers', []);
        $this->watch->start('register-service-providers');
        foreach ($providers as $provider) {
            $this->registerServiceProvider($provider);
        }
        $this->watch->stop('register-service-providers');
    }

    /**
     * Load configured events and listeners
     * @return void
     */
    public function registerConfiguredEvents(): void
    {
        /** @template T @var Config<T> $config */
        $config = $this->get(Config::class);

        /** @var array<string, string[]> $events */
        $events = $config->get('events', []);
        $this->watch->start('register-event-listeners');
        foreach ($events as $eventName => $listeners) {
            foreach ($listeners as $listener) {
                $this->listen($eventName, $listener);
            }
        }
        $this->watch->stop('register-event-listeners');
    }

    /**
     * Load the application configuration
     * @return void
     */
    public function registerConfiguration(): void
    {
        $loader = new FileLoader($this->getConfigPath());
        $config = new Config($loader, $this->env);
        $this->instance($loader);
        $this->instance($config);

        date_default_timezone_set($config->get('app.timezone', 'UTC'));

        //Set the envirnoment
        $this->setEnvironment($config->get('app.env', 'dev'));
    }

    /**
     * Load the environment variables if the file exists
     * @return void
     */
    public function registerEnvironmentVariables(): void
    {
        $this->watch->start('register-environment-variables');
        if (!empty($this->rootPath)) {
            $path = Path::normalizePath($this->rootPath);
            $file = rtrim($path, DIRECTORY_SEPARATOR)
                    . DIRECTORY_SEPARATOR . $this->environmentFile;

            if (is_file($file)) {
                (new Loader())
                    ->load(
                        $file,
                        false,
                        Loader::ENV | Loader::PUTENV
                    );
            }
        }
        $this->watch->stop('register-environment-variables');
    }

    /**
     * Create service provider
     * @param class-string<ServiceProvider> $provider
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
        $this->watch->start('load-core-service-provider');
        $this->registerServiceProvider(new BaseServiceProvider($this));
        $this->registerServiceProvider(new EventServiceProvider($this));
        $this->watch->stop('load-core-service-provider');
    }

    /**
     * Create listener using the container or direct class instance
     * @param string|class-string<ListenerInterface> $listener
     * @return ListenerInterface
     */
    protected function createListener(string $listener): ListenerInterface
    {
        if ($this->has($listener)) {
            return $this->get($listener);
        }

        if (class_exists($listener)) {
            /** @var ListenerInterface $o */
            $o = new $listener();

            return $o;
        }

        throw new InvalidArgumentException(sprintf(
            'Can not resolve the listener class [%s], check if this is the'
                . ' identifier of container or class exists',
            $listener
        ));
    }
}
