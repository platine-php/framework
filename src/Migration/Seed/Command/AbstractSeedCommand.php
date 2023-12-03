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
 *  @file AbstractSeedCommand.php
 *
 *  The Base seed command class
 *
 *  @package    Platine\Framework\Migration\Seed\Command
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Migration\Seed\Command;

use Platine\Config\Config;
use Platine\Console\Command\Command;
use Platine\Database\Connection;
use Platine\Filesystem\DirectoryInterface;
use Platine\Filesystem\FileInterface;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Migration\Seed\AbstractSeed;
use Platine\Stdlib\Helper\Path;
use Platine\Stdlib\Helper\Str;
use RuntimeException;

/**
 * @class AbstractSeedCommand
 * @package Platine\Framework\Migration\Seed\Command
 * @template T
 */
abstract class AbstractSeedCommand extends Command
{
    /**
     * The configuration to use
     * @var Config<T>
     */
    protected Config $config;

    /**
     * The file system to use
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * The Platine Application
     * @var Application
     */
    protected Application $application;

    /**
     * The seed files path
     * @var string
     */
    protected string $seedPath = '';

    /**
     * Create new instance
     * @param Application $app
     * @param Config<T> $config
     * @param Filesystem $filesystem
     */
    public function __construct(
        Application $app,
        Config $config,
        Filesystem $filesystem
    ) {
        parent::__construct('seed', 'Command to manage database seed');
        $this->application = $app;
        $this->config = $config;
        $this->filesystem = $filesystem;
        $path = Path::convert2Absolute($config->get('database.migration.seed_path', 'seeds'));
        $this->seedPath = Path::normalizePathDS($path, true);
    }

    /**
     * Check the seed directory
     * @return void
     */
    protected function checkSeedPath(): void
    {
        $directory = $this->filesystem->directory($this->seedPath);

        if (!$directory->exists() || !$directory->isWritable()) {
            throw new RuntimeException(sprintf(
                'Seed directory [%s] does not exist or is writable',
                $this->seedPath
            ));
        }
    }

    /**
     * Create seed class for the given name
     * @param string $description
     * @return AbstractSeed
     */
    protected function createSeedClass(string $description): AbstractSeed
    {
        $this->checkSeedPath();

        $className = $this->getSeedClassName($description);
        $filename = $this->getFilenameFromClass($className);
        $fullPath = $this->seedPath . $filename;

        $file = $this->filesystem->file($fullPath);
        $fullClassName = 'Platine\\Framework\\Migration\\Seed\\' . $className;

        if (!$file->exists()) {
            throw new RuntimeException(sprintf(
                'Seed file [%s] does not exist',
                $fullPath
            ));
        }

        require_once $fullPath;

        if (!class_exists($fullClassName)) {
            throw new RuntimeException(sprintf(
                'Seed class [%s] does not exist',
                $fullClassName
            ));
        }

        $connection = $this->application->get(Connection::class);
        /** @var AbstractSeed $o */
        $o = new $fullClassName($connection);

        return $o;
    }

    /**
     * Return all seed files available
     * @return array<string, string>
     */
    protected function getAvailableSeeds(): array
    {
        $this->checkSeedPath();

        $directory = $this->filesystem->directory($this->seedPath);
        $result = [];
        /** @var FileInterface[] $files */
        $files = $directory->read(DirectoryInterface::FILE);
        foreach ($files as $file) {
            $matches = [];
            if (preg_match('/^([a-z]+)Seed\.php$/i', $file->getName(), $matches)) {
                $result[Str::camel($matches[1])] = str_replace('_', ' ', Str::snake($matches[1]));
            }
        }

        ksort($result);

        return $result;
    }

    /**
     * Return the seed class name for the given name
     * @param string $description
     * @return string
     */
    protected function getSeedClassName(string $description): string
    {
        $desc = Str::camel($description, false);
        if (!Str::endsWith('Seed', $desc)) {
            $desc .= 'Seed';
        }
        return $desc;
    }

    /**
     * Return the name of the seed file
     * @param string $className
     * @return string
     */
    protected function getFilenameFromClass(string $className): string
    {
        return $filename = sprintf(
            '%s.php',
            $className
        );
    }
}
