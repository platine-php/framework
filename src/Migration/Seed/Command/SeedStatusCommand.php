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
 *  @file SeedStatusCommand.php
 *
 *  The seed status command class
 *
 *  @package    Platine\Framework\Migration\Seed\Command
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Migration\Seed\Command;

use Platine\Config\Config;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;

/**
 * @class SeedStatusCommand
 * @package Platine\Framework\Migration\Seed\Command
 * @template T
 * @extends AbstractSeedCommand<T>
 */
class SeedStatusCommand extends AbstractSeedCommand
{
    /**
     * Create new instance
     * {@inheritdoc}
     */
    public function __construct(
        Application $app,
        Config $config,
        Filesystem $filesystem
    ) {
        parent::__construct($app, $config, $filesystem);
        $this->setName('seed:status')
             ->setDescription('Show your database seeds status');
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $writer = $this->io()->writer();
        $writer->boldYellow('SEED STATUS', true)->eol();
        $writer->bold('Seed path: ');
        $writer->boldBlueBgBlack($this->seedPath, true);

        $seeds = $this->getAvailableSeeds();

        $writer->boldGreen('All seed: ' . count($seeds), true);
        $writer->boldYellow('SEED LIST', true);

        $rows = [];
        foreach ($seeds as $version => $description) {
            $rows[] = [
                'Version' => $version,
                'Seed' => $description
            ];
        }

        $writer->table($rows);

        $writer->boldGreen('Command finished successfully')->eol();
    }
}
