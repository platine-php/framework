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
 *  @file MigrationExecuteCommand.php
 *
 *  The migration execute command class
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
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Migration\MigrationRepository;
use RuntimeException;

/**
 * class MigrationExecuteCommand
 * @package Platine\Framework\Migration\Command
 */
class MigrationExecuteCommand extends AbstractCommand
{

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
        parent::__construct($app, $repository, $schema, $config, $filesystem);
        $this->setName('migration:exec')
             ->setDescription('Execute the migration up/down for one version');

        $this->addArgument('type', 'type of migration [up|down]', 'up', true, true, false, function ($val) {
            if (!in_array($val, ['up', 'down'])) {
                throw new RuntimeException(sprintf(
                    'Invalid argument type [%s], must be one of [up, down]',
                    $val
                ));
            }

             return $val;
        });
    }

    /**
     * {@inheritodc}
     */
    public function execute()
    {
        //$version = $this->getArgumentValue('migrationVersion');
        $type = $this->getArgumentValue('type');

        $io = $this->io();
        $writer = $io->writer();
        $writer->boldYellow('MIGRATION EXECUTION', true)->eol();

        $migrations = $this->getMigrations();
        $executed = $this->getExecuted('DESC');

        if ($type === 'up') {
            $diff = array_diff_key($migrations, $executed);
            if (empty($diff)) {
                $writer->boldGreen('Migration already up to date');
            } else {
                $version = $io->choice('Choose which version to migrate up', $diff);
                $description = str_replace('_', ' ', $migrations[$version]);
                $this->executeMigrationUp($version, $description);
            }
        } else {
            if (empty($executed)) {
                $writer->boldGreen('No migration to rollback');
            } else {
                $data = [];
                foreach ($executed as $version => $entity) {
                    $data[(string)$version] = $entity->description;
                }
                $version = $io->choice('Choose which version to rollback', $data);
                $description = str_replace('_', ' ', $data[$version]);
                $this->executeMigrationDown($version, $description);
            }
        }
    }

    /**
     * Execute migration up
     * @param string $version
     * @param string $description
     * @return void
     */
    public function executeMigrationUp(string $version, string $description): void
    {
        $writer = $this->io()->writer();
        $writer->boldGreen(sprintf(
            '* Execute migration up for %s: %s',
            $version,
            $description,
        ))->eol();

        $migration = $this->createMigrationClass($description, $version);
        $migration->up();

        $entity = $this->repository->create([
            'version' => $version,
            'description' => $description,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->repository->save($entity);
    }

    /**
     * Execute migration down
     * @param string $version
     * @param string $description
     * @return void
     */
    public function executeMigrationDown(string $version, string $description): void
    {
        $writer = $this->io()->writer();
        $writer->boldGreen(sprintf(
            '* Execute migration down for %s: %s',
            $version,
            $description,
        ))->eol();

        $migration = $this->createMigrationClass($description, $version);
        $migration->down();

        $entity = $this->repository->findBy([
            'version' => $version
        ]);

        if ($entity) {
            $this->repository->delete($entity);
        }
    }
}
