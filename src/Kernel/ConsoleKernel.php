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
 *  @file ConsoleKernel.php
 *
 *  The Console Kernel class
 *
 *  @package    Platine\Framework\Kernel
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Kernel;

use InvalidArgumentException;
use Platine\Config\Config;
use Platine\Console\Application as ConsoleApp;
use Platine\Console\Command\Command;
use Platine\Framework\App\Application;

/**
 * class ConsoleKernel
 * @package Platine\Framework\Kernel
 */
class ConsoleKernel
{

    protected ConsoleApp $console;
    protected Application $app;

    /**
     * The list of middlewares
     * @var Command[]
     */
    protected array $commands = [];

    /**
     * Whether the commands already loaded
     * @var bool
     */
    protected bool $commandsLoaded = false;

    /**
     *
     * @param Application $app
     * @param ConsoleApp $console
     */
    public function __construct(Application $app, ConsoleApp $console)
    {
        $this->app = $app;
        $this->console = $console;
    }

    public function run(array $argv): void
    {
        $this->bootstrap();

        foreach ($this->commands as $command) {
            $this->console->addCommand($command);
        }

        $this->console->handle($argv);
    }

    /**
     * Bootstrap the application
     * @return void
     */
    public function bootstrap(): void
    {
        $this->app->registerConfiguration();
        $this->app->registerConfiguredServiceProviders();
        $this->app->boot();

        if (!$this->commandsLoaded) {
            $this->registerConfiguredCommands();

            $this->commandsLoaded = true;
        }
    }

    /**
     *
     * @param string|Command $command
     * @return void
     */
    public function addCommand($command): void
    {
        if (is_string($command)) {
            $command = $this->createCommand($command);
        }

        $this->commands[] = $command;
    }

    /**
     *
     * @return ConsoleApp
     */
    public function getConsoleApp(): ConsoleApp
    {
        return $this->console;
    }

    /**
     * Load configured commands
     * @return void
     */
    protected function registerConfiguredCommands(): void
    {
        /** @template T @var Config<T> $config */
        $config = $this->app->get(Config::class);

        /** @var string[] $commands */
        $commands = $config->get('commands', []);
        foreach ($commands as $command) {
            $this->addCommand($command);
        }
    }

    /**
     * Create new command
     * @param string $command
     * @return Command
     */
    protected function createCommand(string $command): Command
    {
        if ($this->app->has($command)) {
            return $this->app->get($command);
        }

        if (class_exists($command)) {
            return new $command();
        }

        throw new InvalidArgumentException(
            sprintf('The command must be an identifier of container or class')
        );
    }
}
