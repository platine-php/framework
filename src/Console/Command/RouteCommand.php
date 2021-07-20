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
 *  @file RouteCommand.php
 *
 *  The Route management command class
 *
 *  @package    Platine\Framework\Console\Command
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Console\Command;

use Platine\Config\Config;
use Platine\Console\Command\Command;
use Platine\Framework\App\Application;
use Platine\Framework\Service\ServiceProvider;
use Platine\Route\Route;
use Platine\Route\Router;
use Platine\Stdlib\Helper\Arr;
use Platine\Stdlib\Helper\Str;

/**
 * @class RouteCommand
 * @package Platine\Framework\Console\Command
 * @template T
 */
class RouteCommand extends Command
{
    /**
     * The configuration instance
     * @var Config<T>
     */
    protected Config $config;
    protected Router $router;
    protected Application $application;

    /**
     * Create new instance
     * @param Application $application
     * @param Config<T> $config
     * @param Router $router
     */
    public function __construct(Application $application, Config $config, Router $router)
    {
        parent::__construct('route', 'Command to manage route');

        $this->addOption('-l|--list', 'Show route list', null, false);

        $this->router = $router;
        $this->config = $config;
        $this->application = $application;
    }

    /**
     * {@inheritodc}
     */
    public function execute()
    {
        if ($this->getOptionValue('list')) {
            $this->showRouteList();
        }
    }

    /**
     * Show the route list
     * @return void
     */
    protected function showRouteList(): void
    {
        $writer = $this->io()->writer();

        $writer->boldGreen('ROUTE LIST', true)->eol();
        $routeList = $this->config->get('routes', []);
        $routeList[0]($this->router);

        //Load providers routes
        /** @var ServiceProvider[] $providers */
        $providers = $this->application->getProviders();
        foreach ($providers as $provider) {
            $provider->addRoutes($this->router);
        }

        $routes = $this->router->routes()->all();
        $rows = [];
        foreach ($routes as /** @var Route $route */ $route) {
            $handler = Str::stringify($route->getHandler());
            $rows[] = [
                'name' => $route->getName(),
                'method' => implode('|', $route->getMethods()),
                'path' => $route->getPattern(),
                'handler' => $handler,
            ];
        }

        Arr::multisort($rows, 'path');
        $writer->table($rows);
        $writer->green('Command finished successfully')->eol();
    }
}
