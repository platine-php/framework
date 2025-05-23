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
 *  @file AppDatabaseConfig.php
 *
 *  The database configuration class
 *
 *  @package    Platine\Framework\Config
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Config;

use ArrayAccess;
use InvalidArgumentException;
use Platine\Stdlib\Helper\Arr;

/**
 * @class AppDatabaseConfig
 * @package Platine\Framework\Config
 * @template TDbConfigurationEntity as \Platine\Framework\Config\Model\Configuration
 * @implements ArrayAccess<string, mixed>
 */
class AppDatabaseConfig implements ArrayAccess
{
    /**
     * The configuration items loaded
     * @var array<string, mixed>
     */
    protected array $items = [];

    /**
     * Create new configuration instance
     * @param DatabaseConfigLoaderInterface<TDbConfigurationEntity> $loader the loader to use
     * @param string $env the name of the environment
     */
    public function __construct(
        protected DatabaseConfigLoaderInterface $loader,
        protected string $env = ''
    ) {
    }

    /**
     * Check whether the configuration for given key exists
     * @param  string  $key the name of the key
     * @return boolean
     */
    public function has(string $key): bool
    {
        return $this->get($key, $this) !== $this;
    }

    /**
     * Return the configuration value for the given key
     * @param  string $key the name of the configuration item
     * @param  mixed $default the default value if can
     * not find the configuration item
     * @param array<string, mixed>  $filters the filters to use if any
     * @return mixed
     */
    public function get(string $key, mixed $default = null, array $filters = []): mixed
    {
        $results = $this->parseKey($key);
        $group = $results['group'];

        $this->load($group, $filters);

        return Arr::get($this->items, $key, $default);
    }

    /**
     * Set the configuration value for the given key
     * @param  string $key     the name of the configuration item
     * @param  mixed $value the configuration value
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $results = $this->parseKey($key);
        $module = $results['group'];
        $name = $results['key'];

        $config = $this->loader->loadConfig([
             'name' => $name,
             'module' => $module,
             'env' => $this->env
         ]);

        if ($config === null) {
            $this->loader->insertConfig([
                'env' => $this->env,
                'module' => $module,
                'status' => 'Y',
                'name' => $name,
                'value' => $this->getFormattedConfigValue($value),
                'type' => gettype($value)
            ]);
        } else {
            $config->value = $this->getFormattedConfigValue($value);
            $config->type = gettype($value);

            $this->loader->updateConfig($config);
        }
    }

    /**
     * Return all the current loaded configuration items
     * @return array<string, mixed>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Return the configuration current environment
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->env;
    }

    /**
     * Set the configuration environment
     *
     * @param string $env
     * @return $this
     */
    public function setEnvironment(string $env): self
    {
        $this->env = $env;

        return $this;
    }

    /**
     * Return the configuration current loader
     * @return DatabaseConfigLoaderInterface<TDbConfigurationEntity>
     */
    public function getLoader(): DatabaseConfigLoaderInterface
    {
        return $this->loader;
    }

    /**
     * Set the configuration loader
     *
     * @param DatabaseConfigLoaderInterface<TDbConfigurationEntity> $loader
     * @return $this
     */
    public function setLoader(DatabaseConfigLoaderInterface $loader): self
    {
        $this->loader = $loader;

        return $this;
    }

    /**
     * Return all the configuration
     * @return TDbConfigurationEntity[]
     */
    public function all(): array
    {
        return $this->loader->all();
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists(mixed $key): bool
    {
        return $this->has($key);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet(mixed $key): mixed
    {
        return $this->get($key);
    }

    /**
     *
     * @param mixed $key
     * @param mixed $value
     * @return void
     */
    public function offsetSet(mixed $key, mixed $value): void
    {
        $this->set($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset(mixed $key): void
    {
        $this->set($key, null);
    }

    /**
     * Load the configuration
     * @param string $group
     * @param array<string, mixed>  $filters the filters to use if any
     * @return void
     */
    protected function load(string $group, array $filters = []): void
    {
        // If we've already loaded this collection, we will just bail out since we do
        // not want to load it again. Once items are loaded a first time they will
        // stay kept in memory within this class and not loaded from disk again.
        if (isset($this->items[$group])) {
            return;
        }

        $this->items[$group] = $this->loader->load(
            $this->env,
            $group,
            $filters
        );
    }

    /**
     *
     * @param string $key
     * @return array<string, string>
     * @throws InvalidArgumentException
     */
    protected function parseKey(string $key): array
    {
        if (strpos($key, '.') === false) {
            throw new InvalidArgumentException(sprintf(
                'Invalid configuration key [%s] must have format module.name',
                $key
            ));
        }

        $groups = explode('.', $key);

        return [
            'group' => $groups[0],
            'key' => $groups[1],
        ];
    }

    /**
     * Return the formatted configuration value
     * @param mixed $value
     * @return mixed
     */
    protected function getFormattedConfigValue(mixed $value): mixed
    {
        if (is_array($value) || is_object($value)) {
            return serialize($value);
        }

        return $value;
    }
}
