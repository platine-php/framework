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
 *  @file MigrationServiceProvider.php
 *
 *  The Framework database migration service provider class
 *
 *  @package    Platine\Framework\Service\Provider
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Service\Provider;

use Platine\Framework\Migration\Command\MigrationCreateCommand;
use Platine\Framework\Migration\Command\MigrationExecuteCommand;
use Platine\Framework\Migration\Command\MigrationInitCommand;
use Platine\Framework\Migration\Command\MigrationMigrateCommand;
use Platine\Framework\Migration\Command\MigrationResetCommand;
use Platine\Framework\Migration\Command\MigrationStatusCommand;
use Platine\Framework\Migration\MigrationRepository;
use Platine\Framework\Migration\Seed\Command\SeedCreateCommand;
use Platine\Framework\Migration\Seed\Command\SeedCreateDbCommand;
use Platine\Framework\Migration\Seed\Command\SeedExecuteCommand;
use Platine\Framework\Migration\Seed\Command\SeedStatusCommand;
use Platine\Framework\Service\ServiceProvider;

/**
 * @class MigrationServiceProvider
 * @package Platine\Framework\Service\Provider
 */
class MigrationServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $this->app->bind(MigrationRepository::class);
        $this->app->bind(MigrationStatusCommand::class);
        $this->app->bind(MigrationCreateCommand::class);
        $this->app->bind(MigrationExecuteCommand::class);
        $this->app->bind(MigrationMigrateCommand::class);
        $this->app->bind(MigrationResetCommand::class);
        $this->app->bind(MigrationInitCommand::class);
        $this->app->bind(SeedStatusCommand::class);
        $this->app->bind(SeedCreateCommand::class);
        $this->app->bind(SeedCreateDbCommand::class);
        $this->app->bind(SeedExecuteCommand::class);

        //Commands
        $this->addCommand(MigrationStatusCommand::class);
        $this->addCommand(MigrationCreateCommand::class);
        $this->addCommand(MigrationExecuteCommand::class);
        $this->addCommand(MigrationMigrateCommand::class);
        $this->addCommand(MigrationResetCommand::class);
        $this->addCommand(MigrationInitCommand::class);
        $this->addCommand(SeedStatusCommand::class);
        $this->addCommand(SeedExecuteCommand::class);
        $this->addCommand(SeedCreateCommand::class);
        $this->addCommand(SeedCreateDbCommand::class);
    }
}
