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
 *  @file MakeListenerCommand.php
 *
 *  The Make listener Command class
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

/**
 * @class MakeListenerCommand
 * @package Platine\Framework\Console\Command
 */
class MakeListenerCommand extends MakeCommand
{
    /**
     * {@inheritdoc}
     */
    protected string $type = 'event listener';

    /**
     * The event class name
     * @var string
     */
    protected string $eventClass;

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

        $this->setName('make:listener')
               ->setDescription('Command to generate new event listener class');
    }

    /**
     * {@inheritdoc}
     */
    public function interact(Reader $reader, Writer $writer): void
    {
        parent::interact($reader, $writer);
        $io = $this->io();

        $eventClass = $io->prompt('Enter the event full class name', null);
        while (!class_exists($eventClass)) {
            $eventClass = $io->prompt(
                'Class does not exists, please enter the event full class name',
                null
            );
        }

        $this->eventClass = $eventClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassTemplate(): string
    {
        $eventShortName = $this->getClassBaseName($this->eventClass);

        return <<<EOF
        <?php
        
        declare(strict_types=1);
        
        namespace %namespace%;
        
        use Platine\Event\EventInterface;
        use Platine\Event\Listener\ListenerInterface;
        %uses%

        /**
        * @class %classname%
        * @package %namespace%
        */
        class %classname% implements ListenerInterface
        {
            
            /**
            * {@inheritdoc}
            */
            public function handle(EventInterface \$event): mixed
            {
               if (\$event instanceof $eventShortName) {
                   %method_body%
               }
                
               return true;
            }
        }
        
        EOF;
    }

    /**
     * {@inheritdoc}
     */
    protected function getUsesContent(): string
    {
        return $this->getUsesTemplate($this->eventClass);
    }
}
