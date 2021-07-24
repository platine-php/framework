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
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Console;

use Platine\Console\Command\Command;
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
    protected string $name = '';

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

        $this->addArgument('name', 'The full class name (can include root namespace', null, true);
        $this->addOption('-f|--force', 'Overwrite existing files.', false, false);
    }

    /**
     * {@inheritodc}
     */
    public function execute()
    {
        $writer = $this->io()->writer();
        $name = $this->getArgumentValue('name');
        $cleanName = str_replace('/', '\\', $name);

        $this->name = $name;

        $writer->boldGreen(sprintf(
            'Generation of new %s class [%s]',
            $this->type,
            $cleanName
        ), true)->eol();

        $className = $this->getFullClassName($name);
        $path = $this->getPath();
        $namespace = $this->getNamespace();

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

        $this->createParentDirectory($path);
        $this->createClass();
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
        $class = Str::replaceFirst($this->rootNamespace, '', $this->name);

        $path = sprintf(
            '%s/src/%s.php',
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
        $class = str_replace('/', '\\', $this->name);

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
        $className = $this->getFullClassName($this->name);
        $path = $this->getPath();

        return $this->filesystem->file($path)->exists();
    }

    /**
     * Create the class for the given name
     * @return void
     */
    protected function createClass(): void
    {
        $template = $this->getClassTemplate();
        $path = $this->getPath();

        $file = $this->filesystem->file($path);

        $content = $this->replaceNamespace($template);
        $content = $this->replaceClasses($content);
        $file->write($content);
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
            $this->getFullClassName($this->name)
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
     * Replace the classes
     * @param string $content
     * @return string
     */
    protected function replaceClasses(string $content): string
    {
        $shortClassName = $this->getShortClassName();
        $fullClassName = $this->getFullClassName($this->name);

        $replaced = str_replace('%classname%', $shortClassName, $content);

        return str_replace('%fullclassname%', $fullClassName, $replaced);
    }
}
