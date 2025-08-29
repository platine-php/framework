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

namespace Platine\Framework\Helper;

use Platine\Framework\Tool\Database\DatabaseDump;

/**
* @class DatabaseHelper
* @package Platine\Framework\Helper
*/
class DatabaseHelper
{
    /**
     * Create new instance
     * @param DatabaseDump $dbDump
     */
    public function __construct(protected DatabaseDump $dbDump)
    {
    }

    /**
     * Do the database backup
     * @param string $filename
     * @param bool $compress
     * @param array<string, int> $tables
     * @return void
     */
    public function backup(
        string $filename,
        bool $compress = false,
        array $tables = []
    ): void {
        $this->dbDump->setCompress($compress)
                     ->setTables($tables)
                     ->backup($filename);
    }

    /**
     * Do the database restoration
     * @param string $filename
     * @param bool $compress
     * @return void
     */
    public function restore(string $filename, bool $compress = false): void
    {
        $this->dbDump->setCompress($compress)
                     ->restore($filename);
    }
}
