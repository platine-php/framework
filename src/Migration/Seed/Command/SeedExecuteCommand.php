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
 *  @file SeedExecuteCommand.php
 *
 *  The seed execute command class
 *
 *  @package    Platine\Framework\Migration\Seed\Command
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Migration\Seed\Command;

use Platine\Config\Config;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;

/**
 * @class SeedExecuteCommand
 * @package Platine\Framework\Migration\Seed\Command
 * @template T
 * @extends AbstractSeedCommand<T>
 */
class SeedExecuteCommand extends AbstractSeedCommand
{

    /**
     * Create new instance
     * {@inheritodc}
     */
    public function __construct(
        Application $app,
        Config $config,
        Filesystem $filesystem
    ) {
        parent::__construct($app, $config, $filesystem);
        $this->setName('seed:exec')
             ->setDescription('Command to execute seed');
    }

    /**
     * {@inheritodc}
     */
    public function execute()
    {
        $io = $this->io();
        $writer = $io->writer();
        $writer->boldYellow('SEED EXECUTION', true)->eol();

        $seeds = array_values($this->getAvailableSeeds());

        if (empty($seeds)) {
            $writer->boldGreen('No seed available for execution');
        } else {
            $choices = [];
            foreach ($seeds as $key => $seed) {
                $choices[$key + 1] = $seed;
            }
            $index = $io->choice('Choose which seed to execute', $choices);
            if (!isset($seeds[$index - 1])) {
                $writer->boldRed('Please select the correct seed to be executed');
            } else {
                $this->executeSeed($seeds[$index - 1]);
            }
        }
    }

    /**
     * Execute seed
     * @param string $description
     * @return void
     */
    public function executeSeed(string $description): void
    {
        $writer = $this->io()->writer();
        $writer->boldGreen(sprintf(
            '* Execute seed up for %s',
            $description,
        ))->eol();

        $seed = $this->createSeedClass($description);
        $seed->run();
    }
}
