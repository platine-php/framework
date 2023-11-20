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
 *  @file MakeCommand.php
 *
 *  The Make Command base class
 *
 *  @package    Platine\Framework\Console
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Console;

use Platine\Console\Command\Command;
use Platine\Console\Input\Reader;
use Platine\Console\Output\Writer;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Stdlib\Helper\Path;
use Platine\Stdlib\Helper\Str;

/**
 * @class MakeCommand
 * @package Platine\Framework\Console
 */
abstract class MakeCommand extends Command
{
    /**
     * The Application instance
     * @var Application
     */
    protected Application $application;

    /**
     * The file system to use
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * The application root name space
     * @var string
     */
    protected string $rootNamespace;

    /**
     * The type of class
     * @var string
     */
    protected string $type = '';

    /**
     * The class full name given by user
     * @var string
     */
    protected string $className = '';

    /**
     * The action properties
     * @var array<string, array<string, mixed>>
     */
    protected array $properties = [];

    /**
     * Create new instance
     * @param Application $application
     * @param Filesystem $filesystem
     */
    public function __construct(
        Application $application,
        Filesystem $filesystem
    ) {
        parent::__construct('make', 'Command to generate class skeleton');
        $this->application = $application;
        $this->filesystem = $filesystem;
        $this->rootNamespace = $application->getNamespace();
        $this->addArgument('name', 'The full class name (can include root namespace', null, false);
        $this->addOption('-f|--force', 'Overwrite existing files.', false, false);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $io = $this->io();
        $writer = $io->writer();
        $name = $this->className;

        $className = $this->getFullClassName($name);
        $path = $this->getPath();
        $namespace = $this->getNamespace();

        $writer->boldGreen(sprintf(
            'Generation of new %s class [%s]',
            $this->type,
            $className
        ), true)->eol();


        if ($this->fileExists() && !$this->getOptionValue('force')) {
            $writer->red(sprintf(
                'File [%s] already exists.',
                $path
            ), true);

            return;
        }

        $writer->bold('Class: ');
        $writer->boldBlueBgBlack($className, true);

        $writer->bold('Path: ');
        $writer->boldBlueBgBlack($path, true);

        $writer->bold('Namespace: ');
        $writer->boldBlueBgBlack($namespace, true);

        if ($io->confirm(sprintf('Are you confirm the generation of [%s] ?', $className), 'y')) {
            $this->createParentDirectory($path);
            $content = $this->createClass();

            $file = $this->filesystem->file($path);
            $file->write($content);
            $writer->boldGreen(sprintf('Class [%s] generated successfully.', $className), true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function interact(Reader $reader, Writer $writer): void
    {
        $writer->boldYellow('GENERATION OF NEW CLASS', true)->eol();
        $name = $this->getArgumentValue('name');
        if (empty($name)) {
            $io = $this->io();
            $name = $io->prompt('Enter the full class name (can include root namespace)', null);
        }

        $this->className = $name;
    }

    /**
     * Return the base class name
     * @param string|object $fullClassName
     * @return string
     */
    public function getClassBaseName($fullClassName): string
    {
        if (is_object($fullClassName)) {
            $fullClassName = get_class($fullClassName);
        }

        $temp = explode('\\', $fullClassName);

        return end($temp);
    }

    /**
     * Return the the class template
     * @return string
     */
    abstract public function getClassTemplate(): string;

    /**
     * Return the real path for the given name
     * @return string
     */
    protected function getPath(): string
    {
        $class = Str::replaceFirst($this->rootNamespace, '', $this->className);

        $path = sprintf(
            '%s/%s.php',
            $this->application->getAppPath(),
            str_replace('\\', '/', $class)
        );

        return Path::normalizePathDS($path);
    }

    /**
     * Return the class name with the root name space
     * @param string $name
     * @return string
     */
    protected function getFullClassName(string $name): string
    {
        $classClean = ltrim($name, '/\\');
        $class = str_replace('/', '\\', $classClean);

        if (Str::startsWith($this->rootNamespace, $class)) {
            return $class;
        }

        $fullyClass = $this->getFullClassName(sprintf(
            '%s\\%s',
            trim($this->rootNamespace, '\\'),
            $class
        ));

        return $fullyClass;
    }

    /**
     * Return the full name space for the given class
     * @return string
     */
    protected function getNamespace(): string
    {
        $class = str_replace('/', '\\', $this->className);

        return $this->rootNamespace . trim(implode(
            '\\',
            array_slice(explode('\\', $class), 0, -1)
        ), '\\');
    }

    /**
     * Whether the file for the given name already exists
     * @return bool
     */
    protected function fileExists(): bool
    {
        $path = $this->getPath();

        return $this->filesystem->file($path)->exists();
    }

    /**
     * Create the class for the given name
     * @return string
     */
    protected function createClass(): string
    {
        $template = $this->getClassTemplate();

        $replaceNamespace = $this->replaceNamespace($template);
        $replaceUses = $this->replaceClassUses($replaceNamespace);
        $replaceClasses = $this->replaceClasses($replaceUses);
        $replaceProperties = $this->replaceProperties($replaceClasses);
        $replaceConstructor = $this->replaceConstructor($replaceProperties);

        return $replaceConstructor;
    }

    /**
     * Return the short class name
     * @return string
     */
    protected function getShortClassName(): string
    {
        $namespace = $this->getNamespace();

        return Str::replaceFirst(
            $namespace . '\\',
            '',
            $this->getFullClassName($this->className)
        );
    }

    /**
     * Create the class parent(s) directory if it does not exist
     * @param string $path
     * @return void
     */
    protected function createParentDirectory(string $path): void
    {
        $file = $this->filesystem->file($path);
        $location = $file->getLocation();
        if (!empty($location)) {
            $directory = $this->filesystem->directory($location);
            if (!$directory->exists()) {
                $directory->create('', 0775, true);
            }
        }
    }

    /**
     * Replace the name space
     * @param string $content
     * @return string
     */
    protected function replaceNamespace(string $content): string
    {
        $namespace = $this->getNamespace();
        return str_replace('%namespace%', $namespace, $content);
    }

    /**
     * Replace the properties
     * @param string $content
     * @return string
     */
    protected function replaceProperties(string $content): string
    {
        $replaceContent = $this->getPropertiesContent();
        return str_replace('%properties%', $replaceContent, $content);
    }

    /**
     * Replace the constructor
     * @param string $content
     * @return string
     */
    protected function replaceConstructor(string $content): string
    {
        $replaceContent = $this->getConstructorContent();

        return str_replace('%constructor%', $replaceContent, $content);
    }

    /**
     * Replace the class uses instructions
     * @param string $content
     * @return string
     */
    protected function replaceClassUses(string $content): string
    {
        $replaceContent = $this->getUsesContent();
        return str_replace('%uses%', $replaceContent, $content);
    }

    /**
     * Replace the classes
     * @param string $content
     * @return string
     */
    protected function replaceClasses(string $content): string
    {
        $shortClassName = $this->getShortClassName();
        $fullClassName = $this->getFullClassName($this->className);

        $replaced = str_replace('%classname%', $shortClassName, $content);

        return str_replace('%fullclassname%', $fullClassName, $replaced);
    }

    /**
     * Return the properties content
     * @return string
     */
    protected function getPropertiesContent(): string
    {
        if (empty($this->properties)) {
            return '';
        }

        $content = '';

        foreach ($this->properties as $className => $info) {
            $content .= $this->getPropertyTemplate($className, $info);
        }

        return $content;
    }

    /**
     * Return the name space uses content
     * @return string
     */
    protected function getUsesContent(): string
    {
        if (empty($this->properties)) {
            return '';
        }

        $content = '';

        foreach ($this->properties as $className => $info) {
            $content .= $this->getUsesTemplate($className);
        }

        return $content;
    }


    /**
     * Return the constructor content
     * @return string
     */
    protected function getConstructorContent(): string
    {
        if (empty($this->properties)) {
            return '';
        }

        $docblock = $this->getConstructorDocBlockContent();
        $params = $this->getConstructorParamsContent();
        $body = $this->getConstructorBodyContent();

        return <<<EOF
        $docblock
            public function __construct(
               $params
            ){
                $body
            }
        EOF;
    }


    /**
     * Return the constructor document block comment content
     * @return string
     */
    protected function getConstructorDocBlockContent(): string
    {
        $content = '';
        foreach ($this->properties as $className => $info) {
            $content .= $this->getConstructorDocBlockTemplate($className, $info);
        }

        return <<<EOF
        /**
            * Create new instance
            $content*/
        EOF;
    }

    /**
     * Return the constructor parameters content
     * @return string
     */
    protected function getConstructorParamsContent(): string
    {
        $content = '';
        $i = 1;
        $count = count($this->properties);
        foreach ($this->properties as $className => $info) {
            $content .= $this->getConstructorParamsTemplate($className, $info, $i === $count);
            $i++;
        }

        return $content;
    }

    /**
     * Return the constructor body content
     * @return string
     */
    protected function getConstructorBodyContent(): string
    {
        $content = '';
        $i = 1;
        $count = count($this->properties);
        foreach ($this->properties as $className => $info) {
            $content .= $this->getConstructorBodyTemplate($className, $info, $i === $count);
            $i++;
        }

        return $content;
    }

    /**
     * Return the constructor document block template for the given class
     * @param string $className
     * @param array<string, string> $info
     * @return string
     */
    protected function getConstructorDocBlockTemplate(string $className, array $info): string
    {
        $shortClass = $info['short'];
        $name = $info['name'];

        return <<<EOF
        * @param $shortClass \$$name 
            
        EOF;
    }

    /**
     * Return the constructor arguments template for the given class
     * @param string $className
     * @param array<string, string> $info
     * @param bool $isLast
     * @return string
     */
    protected function getConstructorParamsTemplate(
        string $className,
        array $info,
        bool $isLast = false
    ): string {
        $shortClass = $info['short'];
        $name = $info['name'];
        $comma = $isLast ? '' : ',';

        if ($isLast) {
            return <<<EOF
            $shortClass \$$name$comma
            EOF;
        }

        return <<<EOF
        $shortClass \$$name$comma
               
        EOF;
    }

    /**
     * Return the constructor body template for the given class
     * @param string $className
     * @param array<string, string> $info
     * @param bool $isLast
     * @return string
     */
    protected function getConstructorBodyTemplate(string $className, array $info, bool $isLast = false): string
    {
        $name = $info['name'];

        if ($isLast) {
            return <<<EOF
            \$this->$name = \$$name;
            EOF;
        }

        return <<<EOF
        \$this->$name = \$$name;
                
        EOF;
    }

    /**
     * Return the property template for the given class
     * @param string $className
     * @param array<string, mixed> $info
     * @return string
     */
    protected function getPropertyTemplate(string $className, array $info): string
    {
        $shortClass = $info['short'];
        $name = $info['name'];

        return <<<EOF
        /**
            * The $shortClass instance
            * @var $shortClass
            */
            protected $shortClass \$$name;
        
            
        EOF;
    }

    /**
     * Return the name space use template for the given class
     * @param string $className
     * @return string
     */
    protected function getUsesTemplate(string $className): string
    {
        return <<<EOF
        use $className; 
        
        EOF;
    }
}
