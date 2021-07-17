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
 *  @file MigrationMigrateCommand.php
 *
 *  The migration migrate command class
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
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Migration\MigrationRepository;

/**
 * @class MigrationMigrateCommand
 * @package Platine\Framework\Migration\Command
 * @template T
 * @extends AbstractCommand<T>
 */
class MigrationMigrateCommand extends AbstractCommand
{

    /**
     * Create new instance
     * {@inheritodc}
     */
    public function __construct(
        Application $app,
        MigrationRepository $repository,
        Config $config,
        Filesystem $filesystem
    ) {
        parent::__construct($app, $repository, $config, $filesystem);
        $this->setName('migration:migrate')
             ->setDescription('Upgrade migration to latest');
    }

    /**
     * {@inheritodc}
     */
    public function execute()
    {
        $io = $this->io();
        $writer = $io->writer();
        $writer->boldYellow('MIGRATION UPGRADE TO LATEST', true)->eol();

        $migrations = $this->getMigrations();
        $executed = $this->getExecuted();
        $diff = array_diff_key($migrations, $executed);

        if (empty($diff)) {
            $writer->boldGreen('Migration already up to date');
            return;
        }

        $writer->bold('Migration list to be upgraded:', true);
        foreach ($diff as $version => $value) {
            $writer->boldGreen(sprintf(' * %s - %s', $version, $value), true);
        }

        $writer->write('', true);

        if ($io->confirm('Are you confirm the migration upgrade to latest?', 'n')) {
            /** @var MigrationExecuteCommand<T> $migrationExecute */
            $migrationExecute = $this->application->get(MigrationExecuteCommand::class);
            foreach ($diff as $version => $description) {
                $cleanDescription = str_replace('_', ' ', $description);
                $version = (string) $version;
                $migrationExecute->executeMigrationUp($version, $cleanDescription);
            }

            $writer->write('', true);
            $writer->boldGreen('Migration upgraded successfully', true);
        }
    }
}
