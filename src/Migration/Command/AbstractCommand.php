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
 *  @file AbstractCommand.php
 *
 *  The Base migration command class
 *
 *  @package    Platine\Framework\Migration\Command
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Migration\Command;

use Platine\Config\Config;
use Platine\Console\Command\Command;
use Platine\Database\Connection;
use Platine\Filesystem\DirectoryInterface;
use Platine\Filesystem\FileInterface;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Migration\AbstractMigration;
use Platine\Framework\Migration\MigrationRepository;
use Platine\Stdlib\Helper\Path;
use Platine\Stdlib\Helper\Str;
use RuntimeException;

/**
 * @class AbstractCommand
 * @package Platine\Framework\Migration\Command
 * @template T
 */
abstract class AbstractCommand extends Command
{

    /**
     * The migration repository
     * @var MigrationRepository
     */
    protected MigrationRepository $repository;

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
     * The migration files path
     * @var string
     */
    protected string $migrationPath = '';

    /**
     * The migration table
     * @var string
     */
    protected string $table;

    /**
     * Create new instance
     * @param Application $app
     * @param MigrationRepository $repository
     * @param Config<T> $config
     * @param Filesystem $filesystem
     */
    public function __construct(
        Application $app,
        MigrationRepository $repository,
        Config $config,
        Filesystem $filesystem
    ) {
        parent::__construct('migration', 'Command to manage database migration');
        $this->application = $app;
        $this->repository = $repository;
        $this->config = $config;
        $this->filesystem = $filesystem;
        $path = Path::convert2Absolute($config->get('database.migration.path', 'migrations'));
        $this->migrationPath = Path::normalizePathDS($path, true);
        $this->table = $config->get('database.migration.table', 'migrations');
    }

    /**
     * Check the migration directory
     * @return void
     */
    protected function checkMigrationPath(): void
    {
        $directory = $this->filesystem->directory($this->migrationPath);

        if (!$directory->exists() || !$directory->isWritable()) {
            throw new RuntimeException(sprintf(
                'Migration directory [%s] does not exist or is writable',
                $this->migrationPath
            ));
        }
    }

    /**
     * Create migration class for the given version
     * @param string $description
     * @param string $version
     * @return AbstractMigration
     */
    protected function createMigrationClass(
        string $description,
        string $version
    ): AbstractMigration {
        $this->checkMigrationPath();

        $className = $this->getMigrationClassName($description, $version);
        $filename = $this->getFilenameFromClass($className, $version);
        $fullPath = $this->migrationPath . $filename;

        $file = $this->filesystem->file($fullPath);
        $fullClasName = 'Platine\\Framework\\Migration\\' . $className;

        if (!$file->exists()) {
            throw new RuntimeException(sprintf(
                'Migration file [%s] does not exist',
                $fullPath
            ));
        }

        require_once $fullPath;

        if (!class_exists($fullClasName)) {
            throw new RuntimeException(sprintf(
                'Migration class [%s] does not exist',
                $fullClasName
            ));
        }

        $connection = $this->application->get(Connection::class);

        return new $fullClasName($connection);
    }

    /**
     * Return all migrations files available
     * @return array<string, string>
     */
    protected function getMigrations(): array
    {
        $this->checkMigrationPath();

        $directory = $this->filesystem->directory($this->migrationPath);
        $result = [];
        /** @var FileInterface[] $files */
        $files = $directory->read(DirectoryInterface::FILE);
        foreach ($files as $file) {
            $matches = [];
            if (preg_match('/^([0-9_]+)_([a-z_]+)\.php$/i', $file->getName(), $matches)) {
                $result[$matches[1]] = $matches[2];
            }
        }

        ksort($result);

        return $result;
    }

    /**
     * Return the executed migration
     * @param string $orderDir
     * @return array<string, Entity>
     */
    protected function getExecuted(string $orderDir = 'ASC'): array
    {
        $migrations = $this->repository
                           ->query()
                           ->orderBy('version', $orderDir)
                           ->all();
        $result = [];

        foreach ($migrations as $entity) {
            $result[$entity->version] = $entity;
        }

        return $result;
    }

    /**
     * Return the migration class name for the given name
     * @param string $description
     * @param string $version
     * @return string
     */
    protected function getMigrationClassName(string $description, string $version): string
    {
        return Str::camel($description, false)
               . Str::replaceFirst('_', '', $version);
    }

    /**
     * Return the name of the migration file
     * @param string $className
     * @param string $version
     * @return string
     */
    protected function getFilenameFromClass(string $className, string $version): string
    {
        return $filename = sprintf(
            '%s_%s.php',
            $version,
            str_replace(Str::replaceFirst('_', '', $version), '', Str::snake($className))
        );
    }
}
