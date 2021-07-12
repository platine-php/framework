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
 *  @file DatabaseConfigLoader.php
 *
 *  The database configuration loader class
 *
 *  @package    Platine\Framework\Config
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Config;

use Platine\Config\LoaderInterface;
use Platine\Database\QueryBuilder;

/**
 * class DatabaseConfigLoader
 * @package Platine\Framework\Config
 */
class DatabaseConfigLoader implements LoaderInterface
{

    /**
     * The QueryBuilder instance
     * @var QueryBuilder
     */
    protected QueryBuilder $queryBuilder;

    /**
     * The table name
     * @var string
     */
    protected string $table;

    /**
     * Create new instance
     * @param QueryBuilder $queryBuilder
     * @param string $table
     */
    public function __construct(
        QueryBuilder $queryBuilder,
        string $table = 'config'
    ) {
        $this->queryBuilder = $queryBuilder;
        $this->table = $table;
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $environment, string $group): array
    {
        $items = [];

        $environments = $this->parse($environment);
        foreach ($environments as $env) {
            $results = $this->queryBuilder
                          ->from($this->table)
                          ->where('env')->is($env)
                          ->where('module')->is($group)
                          ->where('status')->is(1)
                          ->select()
                          ->fetchObject()
                          ->all();

            if ($results) {
                $config = [];
                foreach ($results as $cfg) {
                    $config = $this->loadConfig($cfg);
                }

                $items = $this->merge($items, $config);
            }
        }

        return $items;
    }

    /**
     * Split the environment at dots or slashes creating
     * an array of name spaces to look through
     *
     * @param  string $env
     * @return array<int, string>
     */
    protected function parse(string $env): array
    {
        $environments = array_filter((array)preg_split('/(\/|\.)/', $env));
        array_unshift($environments, '');

        return $environments;
    }

    /**
     * Merge two array items
     * @param  array<string, mixed>  $items1
     * @param  array<string, mixed>  $items2
     * @return array<string, mixed>
     */
    protected function merge(array $items1, array $items2): array
    {
        return array_replace_recursive($items1, $items2);
    }

    /**
     *
     * @param object[] $results
     * @return array<string, mixed>
     */
    protected function loadConfig(array $results): array
    {
        $config = [];

        var_dump($results);

        foreach ($results as $cfg) {
            if ($cfg->parent) {
                $results = $this->queryBuilder
                              ->from($this->table)
                              ->where('env')->is($cfg->env)
                              ->where('module')->is($cfg->parent)
                              ->where('status')->is(1)
                              ->select()
                              ->fetchObject()
                              ->all();

                foreach ($results as $cfg) {
                    $config[$cfg->name] = $cfg->value ?? $cfg->default_value;
                }
            }
        }
        return $config;
    }
}
