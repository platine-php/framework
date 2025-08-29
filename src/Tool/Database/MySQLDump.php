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

use Exception;
use Platine\Database\Connection;
use Platine\Framework\Helper\NumberHelper;
use Platine\Stdlib\Helper\Str;
use RuntimeException;

/**
 * @class MySQLDump
 * @package Platine\Framework\Tool\Database
 */
class MySQLDump implements DumpDriverInterface
{
    /**
     * {@inheritdoc}
     */
    public function __construct(protected Connection $connection)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function startBackup(string $dbName, array $tables, array $views): string
    {

        // Disable MySQL strict mode
        $this->connection->exec('SET SQL_MODE=""');

        $segments = [...$tables, ...$views];
        if (count($segments) === 0) {
            return '';
        }

        $this->connection->exec('LOCK TABLES `' . implode('` READ, `', $segments) . '` READ');

        $str = '-- Created at ' . date('r') . ' Platine Framework Dump tool' . "\n";
        $str .= '-- Author: Platine Team' . "\n";
        $str .= '-- Database: ' . $dbName . "\n";
        $str .= "\n";
        $str .= 'SET NAMES utf8;' . "\n";
        $str .= 'SET SQL_MODE=\'NO_AUTO_VALUE_ON_ZERO\';' . "\n";
        $str .= 'SET FOREIGN_KEY_CHECKS=0;' . "\n";
        $str .= 'SET UNIQUE_CHECKS=0;' . "\n";
        $str .= 'SET AUTOCOMMIT=0;' . "\n";
        $str .= "\n\n";
        $str .= sprintf('DROP DATABASE IF EXISTS %s;', $dbName) . "\n";
        $str .= sprintf('CREATE DATABASE IF NOT EXISTS %s;', $dbName) . "\n";
        $str .= sprintf('USE %s;', $dbName) . "\n";
        $str .= "\n\n";

        return $str;
    }

    /**
     * {@inheritdoc}
     */
    public function endBackup(string $dbName): string
    {
        $str = 'COMMIT;' . "\n";
        $str .= sprintf('-- THE END [%s]', date('r')) . "\n";

        $this->connection->exec('UNLOCK TABLES');

        return $str;
    }

    /**
     * {@inheritdoc}
     */
    public function dumpTable(string $name, int $mode, bool $isView = false): string
    {
        $quotedTable = $this->quoteName($name);
        $query = $this->connection->query(sprintf('SHOW CREATE TABLE %s', $quotedTable));
        $row = $query->fetchAssoc()->get();

        $result = '-- --------------------------------------------------------' . "\n\n";
        if ($mode & DatabaseDump::DROP) {
            $result .= sprintf('DROP %s IF EXISTS %s;', $isView ? 'VIEW' : 'TABLE', $quotedTable) . "\n\n";
        }

        if ($mode & DatabaseDump::CREATE) {
            $result .= ($row[$isView ? 'Create View' : 'Create Table']) . ";\n\n";
        }

        if ($isView === false && ($mode & DatabaseDump::DATA)) {
            $result .= sprintf('ALTER TABLE %s DISABLE KEYS;', $quotedTable) . "\n\n";
            $numerics = [];
            $query = $this->connection->query(sprintf('SHOW COLUMNS FROM %s', $quotedTable));

            $queryResult = $query->fetchAssoc()->all();
            $columns = [];
            foreach ($queryResult as $res) {
                $column = $res['Field'];
                $columns[] = $this->quoteName($column);
                $numerics[$column] = (bool) preg_match(
                    '#^[^(]*(BYTE|COUNTER|SERIAL|INT|LONG$|CURRENCY|REAL|MONEY|FLOAT|DOUBLE|DECIMAL|NUMERIC|NUMBER)#i',
                    $res['Type']
                );
            }

            $columns = '(' . implode(', ', $columns) . ')';
            $size = 0;
            $queryData = $this->connection->query(sprintf('SELECT * FROM %s', $quotedTable));
            $rows = $queryData->fetchAssoc()->all();
            foreach ($rows as $row) {
                $str = '(';
                foreach ($row as $key => $value) {
                    if ($value === null) {
                        $str .= "NULL,\t";
                    } elseif ($numerics[$key]) {
                        $str .= NumberHelper::numberToString($value) . ",\t";
                    } else {
                        $str .= $this->connection->getDriver()->quote($value) . ",\t";
                    }
                }

                if ($size === 0) {
                    $str = sprintf("INSERT INTO %s %s VALUES\n%s", $quotedTable, $columns, $str);
                } else {
                    $str = ',' . "\n" . $str;
                }

                $length = strlen($str) - 1;
                $str[$length - 1] = ')';

                $result .= $str;
                $size += $length;
                if ($size > DatabaseDump::MAX_SQL_SIZE) {
                    $result .= ";\n";
                    $size = 0;
                }
            }

            if ($size > 0) {
                $result .= ";\n\n";
            }
            $result .= sprintf('ALTER TABLE %s ENABLE KEYS;', $quotedTable) . "\n\n";
            $result .= "\n";
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function restore(
        string $filename,
        string $content,
        ?callable $onProgress = null,
        bool $compress = false
    ): void {
        $sql = '';
        $delimiter = ';';
        $count = 0;
        $lines = (array) explode("\n", $content);
        $total = count($lines);


        foreach ($lines as $str) {
            if (substr($str, 0, 2) === '--') { // This is comment
                continue;
            }

            if (Str::upper(substr($str, 0, 10)) === 'DELIMITER ') {
                $delimiter = trim(substr($str, 10));
            } elseif (substr($ts = rtrim($str), -Str::length($delimiter)) === $delimiter) {
                $sql .= substr($ts, 0, -Str::length($delimiter));

                try {
                    $this->connection->exec($sql);
                } catch (Exception $ex) {
                    throw new RuntimeException(sprintf(
                        'Error when execute the SQL [%s], error [%s]',
                        $sql,
                        $ex->getMessage()
                    ));
                }
                $sql = '';
                $count++;
                if ($onProgress !== null) {
                    call_user_func(
                        $onProgress,
                        $count,
                        $count * 100 / $total
                    );
                }
            } else {
                $sql .= $str;
            }
        }
    }

    /**
     * Do quote of the given name
     * @param string $name
     * @return string
     */
    protected function quoteName(string $name): string
    {
        return $this->connection->getDriver()->quoteIdentifier($name);
    }
}
