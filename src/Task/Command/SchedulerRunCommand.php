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
 *  @file SchedulerRunCommand.php
 *
 *  The scheduler run command class
 *
 *  @package    Platine\Framework\Task\Command
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Task\Command;

use Platine\Config\Config;
use Platine\Framework\App\Application;
use Platine\Framework\Task\SchedulerInterface;

/**
 * @class SchedulerRunCommand
 * @package Platine\Framework\Task\Command
 * @template T
 * @extends AbstractCommand<T>
 */
class SchedulerRunCommand extends AbstractCommand
{
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
        parent::__construct($scheduler, $application, $config);

        $this->setName('scheduler:run')
             ->setDescription('Execute the scheduled tasks');

        $this->addArgument('name', 'the name of task to execute instead of all', null, false);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): mixed
    {
        //Load the task
        $this->loadTasks();

        $name = $this->getArgumentValue('name');
        if (!empty($name)) {
            $task = $this->scheduler->get($name);
            if ($task !== null) {
                $this->scheduler->execute($task);

                return true;
            }
        }

        $this->scheduler->run();

        return true;
    }
}
