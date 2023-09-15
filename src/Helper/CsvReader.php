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
 *  @file CsvReader.php
 *
 *  The CSV Parser reader class
 *
 *  @package    Platine\Framework\Helper
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Helper;

use InvalidArgumentException;

/**
 * @class CsvReader
 * @package Platine\Framework\Helper
 */
class CsvReader
{
    /**
     * The valid delimiters list
     * @var array<string>
     */
    protected array $validDelimiters = [',', ';', "\t", '|', ':'];

    /**
     * The CSV file to be used
     * @var string
     */
    protected string $file;

    /**
     * The parsed CSV data
     * @var array<int, array<string, mixed>>
     */
    protected array $data = [];

    /**
     * The headers columns names
     * @var array<string>
     */
    protected array $headers = [];

    /**
     * Setting to 0 makes the maximum
     * line length not limited
     * @var int<0, max>
     */
    protected int $limit = 0;

    /**
     * The CSV delimiter for each field
     * @var string
     */
    protected string $delimiter = ';';

    /**
     * Return the data list
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Return the total of CSV rows
     * @return int
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Return the header list
     * @return array<string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Return the limit
     * @return int<0, max>
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * Return the delimiter
     * @return string
     */
    public function getDelimiter(): string
    {
        return $this->delimiter;
    }

    /**
     * Set the file to be parsed
     * @param string $file
     * @return $this
     */
    public function setFile(string $file): self
    {
        $this->checkFile($file);

        $this->file = $file;
        return $this;
    }

    /**
     * The parse limit
     * @param int<0, max> $limit
     * @return $this
     */
    public function setLimit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Set the field delimiter
     * @param string $delimiter
     * @return $this
     */
    public function setDelimiter(string $delimiter): self
    {
        if (!in_array($delimiter, $this->validDelimiters)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid delimiter [%s], must be one of [%s]',
                $delimiter,
                implode(',', $this->validDelimiters)
            ));
        }
        $this->delimiter = $delimiter;
        return $this;
    }


    /**
     * Parse the CSV file
     * @return $this
     */
    public function parse(): self
    {
        $fp = fopen($this->file, 'r');
        if ($fp === false) {
            throw new InvalidArgumentException(sprintf(
                'The file [%s] does not exist or readable',
                $this->file
            ));
        }

        $i = 0;
        while (($data = fgetcsv($fp, $this->limit, $this->delimiter)) !== false) {
            if ($data === null) {
                continue;
            }
            // skip all empty lines
            if ($data[0] !== null) {
                if ($i === 0) {
                    $this->headers = array_map([$this, 'sanitize'], $data);
                } else {
                    $result = array_combine($this->headers, $data);
                    if ($result !== false) {
                        $this->data[] = $result;
                    }
                }

                $i++;
            }
        }

        fclose($fp);

        return $this;
    }

    /**
     * Validate the given file
     * @param string $file
     * @return void
     */
    protected function checkFile(string $file): void
    {
        if (file_exists($file) === false) {
            throw new InvalidArgumentException(sprintf(
                'The file [%s] does not exist',
                $file
            ));
        }
    }

    /**
     * Sanitize the given string
     * @param string $value
     * @return string
     */
    private function sanitize(string $value): string
    {
        return (string) preg_replace('/\xEF\xBB\xBF/', '', $value);
    }
}
