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
 *  @file MakeEnumCommand.php
 *
 *  The Make Enumeration Command class
 *
 *  @package    Platine\Framework\Console\Command
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
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
use Platine\Stdlib\Helper\Str;

/**
 * @class MakeEnumCommand
 * @package Platine\Framework\Console\Command
 */
class MakeEnumCommand extends MakeCommand
{
    /**
     * {@inheritdoc}
     */
    protected string $type = 'enum';
    
    /**
     * The enumerations values
     * @var array<string, mixed>
     */
    protected array $enumerations = [];

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
        $this->setName('make:enum')
               ->setDescription('Command to generate new enumeration class');
    }

    /**
     * {@inheritdoc}
     */
    public function interact(Reader $reader, Writer $writer): void
    {
        parent::interact($reader, $writer);

        $properties = [];

        $io = $this->io();
        $writer->boldYellow('Enter the enumeration list (empty value to finish):', true);
        $value = '';
        while ($value !== null) {
            $value = $io->prompt('Enum name', null, null, false);

            if (!empty($value)) {
                $value = trim($value);
                $name = preg_replace('#([^a-z0-9]+)#i', '_', $value);

                $properties[] = Str::upper($name);
            }
        }

        if (!empty($properties)) {
            foreach ($properties as $name) {
                $value = $io->prompt(
                    sprintf('Enumeration value for [%s]', $name),
                    null,
                    null,
                    false
                );
                if (!empty($value)) {
                    $this->enumerations[$name] = $value;
                }
            }
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
        
        %uses%

        /**
        * @class %classname%
        * @package %namespace%
        */
        class %classname%
        {
            %enumerations%
        }
        
        EOF;
    }

    /**
     * {@inheritdoc}
     */
    protected function createClass(): string
    {
        $content = parent::createClass();

        return $this->getEnumerationBody($content);
    }

    /**
     * Return the enumerations body
     * @param string $content
     * @return string
     */
    protected function getEnumerationBody(string $content): string
    {
        $result = '';
        foreach ($this->enumerations as $name => $value) {
            $result .= $this->getEnumerationTemplate($name, $value);
        }

        return str_replace('%enumerations%', $result, $content);
    }

    /**
     * Return the enumeration template
     * @param string $name
     * @param string $value
     * @return string
     */
    protected function getEnumerationTemplate(string $name, string $value): string
    {
        return <<<EOF
        public const $name = '$value';
            
        EOF;
    }

    /**
     * {@inheritdoc}
     */
    protected function getPropertyTemplate(string $className, array $info): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUsesContent(): string
    {
        return '';
    }
}
