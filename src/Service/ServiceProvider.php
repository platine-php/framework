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
 *  @file ServiceProvider.php
 *
 *  The Service provider base class
 *
 *  @package    Platine\Framework\Service
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Service;

use Platine\Framework\App\Application;
use Platine\Route\Router;

/**
 * class ServiceProvider
 * @package Platine\Framework\Service
 */
class ServiceProvider
{

    /**
     * The Application instance
     * @var Application
     */
    protected Application $app;

    /**
     * Provider command list
     * @var class-string[]
     */
    protected array $commands = [];

    /**
     * Create new instance
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Register all the services needed
     * @return void
     */
    public function register(): void
    {
    }

    /**
     * Action to run when the application is booted
     * @return void
     */
    public function boot(): void
    {
    }

    /**
     * Add application route
     * @param Router $router the route instance
     * @return void
     */
    public function addRoutes(Router $router): void
    {
    }

    /**
     * Register command
     * @param class-string $command
     * @return $this
     */
    public function addCommand(string $command): self
    {
        $this->commands[] = $command;

        return $this;
    }

    /**
     * Return the list of command
     * @return class-string[]
     */
    public function getCommands(): array
    {
        return $this->commands;
    }
}
