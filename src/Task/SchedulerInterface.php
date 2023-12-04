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
 *  @file SchedulerInterface.php
 *
 *  The Task scheduler interface class
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

/**
 * @class SchedulerInterface
 * @package Platine\Framework\Task
 */
interface SchedulerInterface
{
    /**
     * Execute the scheduler
     * @return void
     */
    public function run(): void;

    /**
     * Add the given task to the list
     * @param TaskInterface $task
     * @return void
     */
    public function add(TaskInterface $task): void;

    /**
     * Execute the given task
     * @param TaskInterface $task
     * @return void
     */
    public function execute(TaskInterface $task): void;


    /**
     * Return the task for the given key
     * if can not find it the null will be returned
     * @param string $key
     * @return TaskInterface|null
     */
    public function get(string $key): ?TaskInterface;

    /**
     * Return the task list
     * @return TaskInterface[]
     */
    public function all(): array;

    /**
     * Remove the task for the given name
     * @param string $key
     * @return void
     */
    public function remove(string $key): void;

    /**
     * Remove all tasks
     * @return void
     */
    public function removeAll(): void;
}
