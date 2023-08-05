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
 *  @file MakeEntityCommand.php
 *
 *  The Make Entity Command class
 *
 *  @package    Platine\Framework\Console\Command
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Console\Command;

use Platine\Console\Input\Reader;
use Platine\Console\Output\Writer;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Console\MakeCommand;
use Platine\Framework\Task\Cron;
use Platine\Stdlib\Helper\Str;

/**
 * @class MakeTaskCommand
 * @package Platine\Framework\Console\Command
 */
class MakeTaskCommand extends MakeCommand
{
    /**
     * {@inheritdoc}
     */
    protected string $type = 'task';

    /**
     * The name of task
     * @var string
     */
    protected string $name = '';

    /**
     * The task execution expression
     * @var string
     */
    protected string $expression = '* * * * *';

    /**
     * Create new instance
     * @param Application $application
     * @param Filesystem $filesystem
     */
    public function __construct(
        Application $application,
        Filesystem $filesystem
    ) {
        parent::__construct($application, $filesystem);
        $this->setName('make:task')
               ->setDescription('Command to generate new task class');
    }

    /**
     * {@inheritdoc}
     */
    public function interact(Reader $reader, Writer $writer): void
    {
        parent::interact($reader, $writer);


        $io = $this->io();
        $this->name = $io->prompt('Enter the task name', '');


        $expression = $io->prompt('Enter the cron expression', '* * * * *');
        while (Cron::parse($expression) === 0) {
            $expression = $io->prompt('Invalid expression, please enter the cron expression', '* * * * *');
        }
        $this->expression = $expression;

        $properties = [];

        $writer->boldYellow('Enter the properties list (empty value to finish):', true);
        $value = '';
        while ($value !== null) {
            $value = $io->prompt('Property full class name', null, null, false);

            if (!empty($value)) {
                $value = trim($value);
                if (!class_exists($value) && !interface_exists($value)) {
                    $writer->boldWhiteBgRed(sprintf('The class [%s] does not exists', $value), true);
                } else {
                    $shortClass = basename($value);
                    $name = Str::camel($shortClass, true);
                    //replace"interface", "abstract"
                    $nameClean = str_ireplace(['interface', 'abstract'], '', $name);

                    $properties[$value] = [
                        'name' => $nameClean,
                        'short' => $shortClass,
                    ];
                }
            }
        }
        $this->properties = $properties;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassTemplate(): string
    {
        return <<<EOF
        <?php
        
        declare(strict_types=1);
        
        namespace %namespace%;
        
        use Platine\Framework\Task\TaskInterface;
        %uses%

        /**
        * @class %classname%
        * @package %namespace%
        */
        class %classname% implements TaskInterface
        {
            
            %properties%
        
            %constructor%
        
            /**
            * {@inheritdoc}
            */
            public function run(): void
            {
                
            }
        
            %task_body%
        }
        
        EOF;
    }

    /**
     * {@inheritdoc}
     */
    protected function createClass(): string
    {
        $content = parent::createClass();

        return $this->getTaskBody($content);
    }

    /**
     * Return the task body
     * @param string $content
     * @return string
     */
    protected function getTaskBody(string $content): string
    {
        $result = <<<EOF
            /**
            * {@inheritdoc}
            */
            public function expression(): string
            {
                return '$this->expression';
            }
                    
            /**
            * {@inheritdoc}
            */
            public function name(): string
            {
                return '$this->name';
            }
        EOF;

        return str_replace('%task_body%', $result, $content);
    }
}
