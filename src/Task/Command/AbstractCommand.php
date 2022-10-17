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
 *  @file AbstractCommand.php
 *
 *  The Base scheduler command class
 *
 *  @package    Platine\Framework\Task\Command
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Task\Command;

use InvalidArgumentException;
use Platine\Config\Config;
use Platine\Console\Command\Command;
use Platine\Framework\App\Application;
use Platine\Framework\Task\SchedulerInterface;
use Platine\Framework\Task\TaskInterface;

/**
 * @class AbstractCommand
 * @package Platine\Framework\Task\Command
 */
abstract class AbstractCommand extends Command
{
    /**
     * The scheduler instance
     * @var SchedulerInterface
     */
    protected SchedulerInterface $scheduler;

    /**
     * The Application instance
     * @var Application
     */
    protected Application $application;

    /**
     * The configuration instance
     * @var Config<T>
     */
    protected Config $config;

    /**
     * Create new instance
     * @param SchedulerInterface $scheduler
     * @param Application $application
     * @param Config<T> $config
     */
    public function __construct(
        SchedulerInterface $scheduler,
        Application $application,
        Config $config
    ) {
        parent::__construct('scheduler', 'Command to manage scheduler tasks');
        $this->scheduler = $scheduler;
        $this->application = $application;
        $this->config = $config;
    }

    /**
     * Add new task
     * @param string|TaskInterface $task
     * @return void
     */
    protected function addTask($task): void
    {
        if (is_string($task)) {
            $task = $this->createTask($task);
        }

        $this->scheduler->add($task);
    }

    /**
     * Load configured tasks
     * @return void
     */
    protected function registerConfiguredTasks(): void
    {
        /** @var string[] $tasks */
        $tasks = $this->config->get('tasks', []);
        foreach ($tasks as $task) {
            $this->addTask($task);
        }
    }

    /**
     * Create new task
     * @param string $task
     * @return TaskInterface
     */
    protected function createTask(string $task): TaskInterface
    {
        if ($this->application->has($task)) {
            return $this->application->get($task);
        }

        if (class_exists($task)) {
            return new $task();
        }

        throw new InvalidArgumentException(
            sprintf('The task [%s] must be an identifier of container or class', $task)
        );
    }

    /**
     * Load the task list from configuration and services
     * providers
     * @return void
     */
    protected function loadTasks(): void
    {
        $this->registerConfiguredTasks();

        //Load providers tasks
        /** @var class-string[] $tasks */
        $tasks = $this->application->getProvidersTasks();
        foreach ($tasks as $task) {
            $this->addTask($task);
        }
    }
}
