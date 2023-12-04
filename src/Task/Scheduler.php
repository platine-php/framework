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
 *  @file Scheduler.php
 *
 *  The Scheduler Task class
 *
 *  @package    Platine\Framework\Task
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Task;

use DateTime;
use Platine\Logger\LoggerInterface;
use RuntimeException;
use Throwable;

/**
 * @class Scheduler
 * @package Platine\Framework\Task
 */
class Scheduler implements SchedulerInterface
{
    /**
     * The logger instance
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * The task list
     * @var TaskInterface[]
     */
    protected array $tasks = [];

    /**
     * Create new instance
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        $datetime = new DateTime();
        $tasks = $this->tasks;
        foreach ($tasks as $task) {
            if ($this->isDue($task, $datetime)) {
                $this->execute($task);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function add(TaskInterface $task): void
    {
        $key = $task->name();
        if (array_key_exists($key, $this->tasks)) {
            throw new RuntimeException(sprintf('Task [%s] already exist', $key));
        }
        $this->tasks[$key] = $task;
    }


    /**
     * {@inheritdoc}
     */
    public function get(string $key): ?TaskInterface
    {
        return $this->tasks[$key] ?? null;
    }


    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        return $this->tasks;
    }

    /**
     * {@inheritdoc}
     */
    public function remove(string $key): void
    {
        unset($this->tasks[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function removeAll(): void
    {
        $this->tasks = [];
    }

    /**
     * {@inheritdoc}
     */
    public function execute(TaskInterface $task): void
    {
        $this->logger->info('Execute task {task}', ['task' => $task->name()]);
        try {
            $task->run();
        } catch (Throwable $e) {
            $this->logger->error('Error occurred when execute task {task}, error: {error}', [
               'task' => $task->name(),
               'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Whether the given task is due
     * @param TaskInterface $task
     * @param DateTime $datetime
     * @return bool
     */
    protected function isDue(TaskInterface $task, DateTime $datetime): bool
    {
        $expression = $task->expression();
        $time = Cron::parse($expression);
        $cronDatetime = date('Y-m-d H:i', $time);

        return $datetime->format('Y-m-d H:i') === $cronDatetime;
    }
}
