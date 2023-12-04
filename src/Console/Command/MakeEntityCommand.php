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

/**
 * @class MakeEntityCommand
 * @package Platine\Framework\Console\Command
 */
class MakeEntityCommand extends MakeCommand
{
    /**
     * {@inheritdoc}
     */
    protected string $type = 'entity';

    /**
     * Whether to use timestamp feature
     * @var bool
     */
    protected bool $useTimestamp = false;

    /**
     * The name of field for created at
     * @var string
     */
    protected string $createdAtField = 'created_at';

    /**
     * The name of field for updated at
     * @var string
     */
    protected string $upatedAtField = 'updated_at';

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
        $this->setName('make:entity')
               ->setDescription('Command to generate new entity class');
    }

    /**
     * {@inheritdoc}
     */
    public function interact(Reader $reader, Writer $writer): void
    {
        parent::interact($reader, $writer);


        $io = $this->io();

        $this->useTimestamp = $io->confirm('Use timestamp feature', 'y');

        if ($this->useTimestamp) {
            $this->createdAtField = $io->prompt('Created at field name', 'created_at');
            $this->upatedAtField = $io->prompt('Updated at field name', 'updated_at');
        }
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
        
        use Platine\Orm\Entity;
        use Platine\Orm\Mapper\EntityMapperInterface;
        
        %uses%

        /**
        * @class %classname%
        * @package %namespace%
        * @extends Entity<%classname%>
        */
        class %classname% extends Entity
        {
            
            /**
            * @param EntityMapperInterface<%classname%> \$mapper
            * @return void
            */
            public static function mapEntity(EntityMapperInterface \$mapper): void
            {
             %mapper_body%
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

        return $this->getMapperBody($content);
    }

    /**
     * Return the mapper body
     * @param string $content
     * @return string
     */
    protected function getMapperBody(string $content): string
    {
        $result = '';
        if ($this->useTimestamp) {
            $useTimestamp = 'useTimestamp()';
            if ($this->createdAtField !== 'created_at' || $this->upatedAtField !== 'updated_at') {
                $useTimestamp = sprintf(
                    'useTimestamp(true, \'%s\', \'%s\')',
                    $this->createdAtField,
                    $this->upatedAtField
                );
            }
            $result = <<<EOF
            \$mapper->$useTimestamp;
                 \$mapper->casts([
                    '$this->createdAtField' => 'date',
                    '$this->upatedAtField' => '?date',
                 ]);
        EOF;
        }

        return str_replace('%mapper_body%', $result, $content);
    }
}
