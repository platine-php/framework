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
 *  @file MakeRepositoryCommand.php
 *
 *  The Make Repository Command class
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
 * @class MakeRepositoryCommand
 * @package Platine\Framework\Console\Command
 */
class MakeRepositoryCommand extends MakeCommand
{
    /**
     * {@inheritdoc}
     */
    protected string $type = 'repository';

    /**
     * The entity class name
     * @var string
     */
    protected string $entityClass;

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
        $this->setName('make:repository')
               ->setDescription('Command to generate new repository class');
    }

    /**
     * {@inheritdoc}
     */
    public function interact(Reader $reader, Writer $writer): void
    {
        parent::interact($reader, $writer);


        $io = $this->io();

        $entityClass = $io->prompt('Enter the entity full class name', null);
        while (!class_exists($entityClass)) {
            $entityClass = $io->prompt('Class does not exists, please enter the entity full class name', null);
        }

        $this->entityClass = $entityClass;
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
        
        use Platine\Orm\EntityManager;
        use Platine\Orm\Repository;
        %uses%

        /**
        * @class %classname%
        * @package %namespace%
        * @extends Repository<%entity_base_class%>
        */
        class %classname% extends Repository
        {
            
            /**
            * Create new instance
            * @param EntityManager<%entity_base_class%> \$manager
            */
           public function __construct(EntityManager \$manager)
           {
               parent::__construct(\$manager, %entity_class%);
           }
        }
        
        EOF;
    }

    /**
     * {@inheritdoc}
     */
    protected function createClass(): string
    {
        $content = parent::createClass();

        return $this->getEntityBody($content);
    }

    /**
     * {@inheritdoc}
     */
    protected function getUsesContent(): string
    {
        return $this->getUsesTemplate($this->entityClass);
    }

    /**
     * Return the entity body
     * @param string $content
     * @return string
     */
    protected function getEntityBody(string $content): string
    {
        $entityName = $this->getClassBaseName($this->entityClass) . '::class';
        $entityBaseName = $this->getClassBaseName($this->entityClass);

        $template = str_replace('%entity_base_class%', $entityBaseName, $content);
        return str_replace('%entity_class%', $entityName, $template);
    }
}
