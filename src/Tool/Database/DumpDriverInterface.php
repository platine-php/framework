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

/**
 * @class DumpDriverInterface
 * @package Platine\Framework\Tool\Database
 */
interface DumpDriverInterface
{
    /**
     * Create new instance
     * @param Connection $connection
     */
    public function __construct(Connection $connection);

    /**
     * This will execute when start the backup
     * @param string $dbName
     * @param array<string> $tables
     * @param array<string> $views
     * @return string
     */
    public function startBackup(string $dbName, array $tables, array $views): string;

    /**
     * This will execute at the end of the backup
     * @param string $dbName
     * @return string
     */
    public function endBackup(string $dbName): string;

    /**
     * Dump the given table or view
     * @param string $name
     * @param int $mode
     * @param bool $isView
     *
     * @return string
     */
    public function dumpTable(string $name, int $mode, bool $isView = false): string;

    /**
     * Restore the database using the given filename or content
     * @param string $filename
     * @param string $content
     * @param callable|null $onProgress the function to call for progress
     * @param bool $compress whether the filename is compressed
     *
     * @return void
     */
    public function restore(
        string $filename,
        string $content,
        ?callable $onProgress = null,
        bool $compress = false
    ): void;
}
