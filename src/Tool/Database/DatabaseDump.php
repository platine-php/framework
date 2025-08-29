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

declare(strict_types=1);

namespace Platine\Framework\Tool\Database;

use Platine\Database\Connection;
use Platine\Filesystem\Filesystem;
use Platine\Logger\LoggerInterface;
use RuntimeException;

/**
 * @class DatabaseDump
 * @package Platine\Framework\Tool\Database
 */
class DatabaseDump
{
    /**
     * Do not do backup of anything
     */
    public const NONE = 0;

    /**
     * Add "DROP" statement for database, tables, etc.
     */
    public const DROP = 1;

    /**
     * Add statement for database, table, etc. creation
     */
    public const CREATE = 2;

    /**
     * Do backup of data
     */
    public const DATA = 4;

    /**
     * Do backup of triggers
     */
    public const TRIGGER = 8; // not used currently

    /**
     * Default value, process for all
     */
    public const ALL = 15;

    /**
     * The maximum SQL size
     */
    public const MAX_SQL_SIZE = 1e6;

    /**
     * The table list to process
     * @var array<string, int>
     */
    protected array $tables = ['*' => self::ALL];

    /**
     * Whether to compress backup file
     * @var bool
     */
    protected bool $compress = false;

    /**
     * Callback to track the progress
     * @var callable|null
     */
    protected $onProgress = null;

    /**
     * The dump driver
     * @var DumpDriverInterface
     */
    protected DumpDriverInterface $driver;

    /**
    * Create new instance
    * @param Connection $connection
    * @param LoggerInterface $logger
    * @param Filesystem $filesystem
    */
    public function __construct(
        protected Connection $connection,
        protected LoggerInterface $logger,
        protected Filesystem $filesystem
    ) {
        $this->createDriver();
    }

    /**
     * Save the database data into the given file
     * @param string $filename
     * @return void
     */
    public function backup(string $filename): void
    {
        $file = $this->filesystem->file($filename);
        if ($file->exists() && $file->isWritable() === false) {
            throw new RuntimeException(sprintf('The file [%s] is not writable', $filename));
        }

        $schema = $this->connection->getSchema();
        $tables = array_keys($schema->getTables());
        $views = array_keys($schema->getViews());
        $dbName = $schema->getDatabaseName();

        $content = $this->driver->startBackup($dbName, $tables, $views);

        foreach ($tables as $name) {
            $content .= $this->dumpTable($name, false);
        }

        foreach ($views as $name) {
            $content .= $this->dumpTable($name, true);
        }

        $content .= $this->driver->endBackup($dbName);

        if ($this->compress) {
            $content = (string) gzencode($content);
        }

        $file->write($content);
    }

    /**
     * Restore the database data using the given file
     * @param string $filename
     * @return void
     */
    public function restore(string $filename): void
    {
        $file = $this->filesystem->file($filename);
        if ($file->exists() === false || $file->isReadable() === false) {
            throw new RuntimeException(sprintf(
                'The file [%s] does not exist or is not readable',
                $filename
            ));
        }

        $content = $file->read();
        if ($this->compress) {
            $content = (string) gzdecode($content);
        }

        $this->driver->restore(
            $filename,
            $content,
            $this->onProgress,
            $this->compress
        );
    }

    /**
     * Whether compression is used or not
     * @return bool
     */
    public function isCompress(): bool
    {
        return $this->compress;
    }

    /**
     * Return the on progress handler
     * @return callable|null
     */
    public function getOnProgress(): ?callable
    {
        return $this->onProgress;
    }

    /**
     * Set the tables
     * @param array<string, int> $tables
     * @return $this
     */
    public function setTables(array $tables)
    {
        if (!isset($tables['*'])) {
            $tables['*'] = self::ALL;
        }

        $this->tables = $tables;
        return $this;
    }

    /**
     * Set the compression feature
     * @param bool $compress
     * @return $this
     */
    public function setCompress(bool $compress): self
    {
        if ($compress && function_exists('gzopen') === false) {
            throw new RuntimeException(sprintf(
                '"%s" function does not exist can not use compression',
                'gzopen'
            ));
        }

        $this->compress = $compress;

        return $this;
    }

    /**
     * Set on progress handler
     * @param callable|null $onProgress
     * @return $this
     */
    public function setOnProgress(?callable $onProgress): self
    {
        $this->onProgress = $onProgress;
        return $this;
    }

    /**
     * Dump the given table or view
     * @param string $name
     * @param bool $isView
     * @return string
     */
    protected function dumpTable(string $name, bool $isView = false): string
    {
        $mode = $this->tables[$name] ?? $this->tables['*'];
        if ($mode === self::NONE) {
            return '';
        }

        return $this->driver->dumpTable($name, $mode, $isView);
    }

    /**
     * Create the dump driver
     * @return void
     */
    protected function createDriver(): void
    {
        $maps = [
          'mysql'  => MySQLDump::class,
        ];

        $driver = $this->connection->getConfig()->getDriverName();
        $className = $maps[$driver] ?? NullDumpDriver::class;

        $this->driver = new $className($this->connection);
    }
}
