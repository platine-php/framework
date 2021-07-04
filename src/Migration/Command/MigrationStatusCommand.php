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
 *  @file MigrationStatusCommand.php
 *
 *  The migration status command class
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
use Platine\Framework\Migration\Command\AbstractCommand;
use Platine\Framework\Migration\MigrationRepository;

/**
 * class MigrationStatusCommand
 * @package Platine\Framework\Migration\Command
 */
class MigrationStatusCommand extends AbstractCommand
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
        $this->setName('migration:status')
             ->setDescription('Show current status of your migrations');
    }

    /**
     * {@inheritodc}
     */
    public function execute()
    {
        $writer = $this->io()->writer();
        $writer->boldYellow('MIGRATION STATUS', true)->eol();
        $writer->bold('Migration path: ');
        $writer->boldBlueBgBlack($this->migrationPath, true);
        $writer->bold('Migration table: ');
        $writer->boldBlueBgBlack($this->table, true);

        $migrations = $this->getMigrations();
        $executed = $this->getExecuted();
        $diff = array_diff_key($migrations, $executed);

        $writer->boldGreen('Migration All: ' . count($migrations), true);
        $writer->boldGreen('Migration Available: ' . count($diff), true);
        $writer->boldGreen('Migration Executed: ' . count($executed), true)->eol();

        $writer->boldYellow('MIGRATION LIST', true);

        $rows = [];
        foreach ($executed as $version => $entity) {
            $rows[] = [
                'version' => (string) $version,
                'description' => $entity->description,
                'date' => $entity->created_at,
                'status' => 'UP'
            ];
        }

        foreach ($diff as $version => $description) {
            $cleanDescription = str_replace('_', ' ', $description);
            $version = (string) $version;
            $rows[] = [
                'version' => $version,
                'description' => $cleanDescription,
                'date' => '',
                'status' => 'DOWN'
            ];
        }
        $writer->table($rows);

        $writer->boldGreen('Command finished successfully')->eol();
    }
}
