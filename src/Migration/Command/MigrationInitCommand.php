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
 *  @file MigrationInitCommand.php
 *
 *  The migration initialize command class
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
use Platine\Database\Schema;
use Platine\Database\Schema\CreateTable;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Migration\MigrationRepository;

/**
 * @class MigrationInitCommand
 * @package Platine\Framework\Migration\Command
 * @template T
 * @extends AbstractCommand<T>
 */
class MigrationInitCommand extends AbstractCommand
{
    /**
     * The schema to use
     * @var Schema
     */
    protected Schema $schema;

    /**
     * Create new instance
     * {@inheritodc}
     */
    public function __construct(
        Application $app,
        MigrationRepository $repository,
        Schema $schema,
        Config $config,
        Filesystem $filesystem
    ) {
        parent::__construct($app, $repository, $config, $filesystem);
        $this->setName('migration:init')
             ->setDescription('Initialize the migration by creating migration table');

        $this->schema = $schema;
    }

    /**
     * {@inheritodc}
     */
    public function execute()
    {
        $io = $this->io();
        $writer = $io->writer();
        $writer->boldYellow('MIGRATION INITIALIZATION', true);

        if ($this->schema->hasTable($this->table, true)) {
            $writer->boldRed(sprintf(
                'Migration table [%s] already created',
                $this->table
            ));
            return;
        }

        $this->createMigrationTable();
        $writer->boldGreen(sprintf(
            'Migration table [%s] created successfully',
            $this->table
        ));
    }

    /**
     * Create migration table
     * @return void
     */
    protected function createMigrationTable(): void
    {
        $tableName = $this->table;
        $this->schema->create($tableName, function (CreateTable $table) {
            $table->string('version', 20)
                   ->description('The migration version')
                   ->primary();
            $table->string('description')
                   ->description('The migration description');
            $table->datetime('created_at')
                    ->description('Migration run time');

            $table->engine('INNODB');
        });
    }
}
