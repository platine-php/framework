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
 *  @file BaseCommand.php
 *
 *  The Base Command class
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

use InvalidArgumentException;
use Platine\Console\Command\Command;
use Platine\Console\Input\Reader;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Stdlib\Helper\Path;
use Platine\Stdlib\Helper\Str;

/**
 * @class BaseCommand
 * @package Platine\Framework\Console
 */
abstract class BaseCommand extends Command
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
     * Return the real path for the given name
     * @param string $className
     * @return string
     */
    public function getPath(string $className): string
    {
        $class = Str::replaceFirst($this->rootNamespace, '', $className);

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
    public function getFullClassName(string $name): string
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
     * @param string $className
     * @return string
     */
    public function getNamespace(string $className): string
    {
        $class = str_replace('/', '\\', $className);

        return $this->rootNamespace . trim(implode(
            '\\',
            array_slice(explode('\\', $class), 0, -1)
        ), '\\');
    }

    /**
     * Whether the file for the given name already exists
     * @param string $className
     * @return bool
     */
    public function fileExists(string $className): bool
    {
        $path = $this->getPath($className);

        return $this->filesystem->file($path)->exists();
    }

    /**
     * Return the short class name
     * @param string $className
     * @return string
     */
    public function getShortClassName(string $className): string
    {
        $namespace = $this->getNamespace($className);

        return Str::replaceFirst(
            $namespace . '\\',
            '',
            $this->getFullClassName($className)
        );
    }

    /**
     * Create the class parent(s) directory if it does not exist
     * @param string $path
     * @return void
     */
    public function createParentDirectory(string $path): void
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
     * Set reader stream content
     * @param Reader $reader
     * @param string $filename
     * @param string|array<string> $data
     * @return void
     */
    public function setReaderContent(Reader $reader, string $filename, $data): void
    {
        if (is_array($data)) {
            $data = implode(PHP_EOL, $data);
        }
        file_put_contents($filename, $data, FILE_APPEND);

        $resource = fopen($filename, 'r');
        if ($resource === false) {
            throw new InvalidArgumentException(sprintf('Could not open filename [%s] for reading', $filename));
        }
        $reader->setStream($resource);
    }
}
