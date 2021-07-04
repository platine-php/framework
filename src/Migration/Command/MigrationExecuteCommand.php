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
             ->setDescription('Upgrade the migration up/down for the given version');

        $this->addArgument('migration version', 'Version to execute', '', true);
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
        $version = $this->getArgumentValue('migrationVersion');
        $type = $this->getArgumentValue('type');

        $writer = $this->io()->writer();
        $writer->boldYellow('MIGRATION EXECUTION', true)->eol();
        $writer->bold('Version: ');
        $writer->boldBlueBgBlack($version, true);
        $writer->bold('Type: ');
        $writer->boldBlueBgBlack($type, true);

        $migrations = $this->getMigrations();
        if (!array_key_exists($version, $migrations)) {
            throw new RuntimeException(sprintf(
                'Invalid migration version [%s]',
                $version
            ));
        }
        $description = str_replace('_', ' ', $migrations[$version]);

        $writer->bold('Description: ');
        $writer->boldBlueBgBlack($description, true)->eol();

        if ($type === 'up') {
            $this->executeMigrationUp($version, $description);
        } else {
            $this->executeMigrationDown($version, $description);
        }

        $writer->boldGreen(sprintf(
            'Migration [%s] executed successfully',
            $description
        ))->eol();
    }

    /**
     * Execute migration up
     * @param string $version
     * @param string $description
     * @return void
     */
    public function executeMigrationUp(string $version, string $description): void
    {
        $exists = $this->repository->findBy([
            'version' => $version
        ]);

        if ($exists) {
            throw new RuntimeException(sprintf(
                'Migration with version [%s], already executed at [%s]',
                $version,
                $exists->created_at
            ));
        }
        $writer = $this->io()->writer();
        $writer->boldGreen(sprintf(
            '* Execute migration %s: %s',
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
