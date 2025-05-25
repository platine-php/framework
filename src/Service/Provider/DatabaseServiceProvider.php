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
 *  @file DatabaseServiceProvider.php
 *
 *  The Framework database service provider class
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
use Platine\Database\Configuration;
use Platine\Database\Connection;
use Platine\Database\Pool;
use Platine\Database\QueryBuilder;
use Platine\Database\Schema;
use Platine\Framework\Service\ServiceProvider;
use Platine\Logger\LoggerInterface;
use Platine\Orm\EntityManager;

/**
 * @class DatabaseServiceProvider
 * @package Platine\Framework\Service\Provider
 */
class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $this->app->bind(Configuration::class, function (ContainerInterface $app) {
            /** @template T @var Config<T> $config */
            $config = $app->get(Config::class);
            $driver = $config->get('database.default', 'default');
            $slowQueryTime = $config->get('database.slow_query_time', 1.0);
            $dbConfig = $config->get(sprintf('database.connections.%s', $driver), []);

            // Pass the slow query time and name to driver connection
            $dbConfig['slow_query_time'] = $slowQueryTime;
            $dbConfig['name'] = $driver;

            return new Configuration($dbConfig);
        });

        $this->app->share(Pool::class, function (ContainerInterface $app) {
            return new Pool($app->get(Config::class)->get('database', []));
        });

        $this->app->share(Connection::class, function (ContainerInterface $app) {
            return new Connection(
                $app->get(Configuration::class),
                $app->get(LoggerInterface::class)
            );
        });
        $this->app->bind(EntityManager::class);
        $this->app->bind(QueryBuilder::class);
        $this->app->bind(Schema::class);
    }
}
