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
 * @class SeedExecuteCommand
 * @package Platine\Framework\Migration\Seed\Command
 * @template T
 * @extends AbstractSeedCommand<T>
 */
class SeedExecuteCommand extends AbstractSeedCommand
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

        $this->setName('seed:exec')
             ->setDescription('Command to execute seed');

        $this->addOption('-i|--id', 'the seed version', null, false);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): mixed
    {
        $io = $this->io();
        $writer = $io->writer();
        $writer->boldYellow('SEED EXECUTION', true)->eol();

        $seeds = $this->getAvailableSeeds();

        if (count($seeds) === 0) {
            $writer->boldGreen('No seed available for execution');
        } else {
            $version = $this->getOptionValue('id');
            if ($version !== null) {
                if (array_key_exists($version, $seeds) === false) {
                    $writer->boldRed(sprintf(
                        'Invalid seed version [%s]',
                        $version
                    ));
                } else {
                    $this->executeSeed($seeds[$version], $version);
                }
            } else {
                foreach ($seeds as $version => $seed) {
                    $this->executeSeed($seed, $version);
                }
            }
        }

        $writer->eol();
        $writer->boldGreen('Command finished successfully')->eol();

        return true;
    }

    /**
     * Execute seed
     * @param string $description
     * @param string $version
     * @return void
     */
    public function executeSeed(string $description, string $version): void
    {
        $writer = $this->io()->writer();
        $writer->boldGreen(sprintf(
            '* Execute seed for %s: %s',
            $version,
            $description
        ))->eol();

        $seed = $this->createSeedClass($description, $version);
        $seed->run();
    }
}
