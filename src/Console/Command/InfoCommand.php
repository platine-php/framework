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
 *  @file InfoCommand.php
 *
 *  The Platine deployment information command class
 *
 *  @package    Platine\Framework\Console\Command
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Console\Command;

use Platine\Config\Config;
use Platine\Console\Command\Command;
use Platine\Framework\App\Application;
use Platine\Stdlib\Helper\Arr;
use Platine\Stdlib\Helper\Composer;
use Platine\Stdlib\Helper\Str;

/**
 * @class InfoCommand
 * @package Platine\Framework\Console\Command
 * @template T
 */
class InfoCommand extends Command
{
    /**
     * Create new instance
     * @param Config<T> $config
     * @param Application $application
     */
    public function __construct(
        protected Config $config,
        protected Application $application
    ) {
        parent::__construct('info', 'Command to show deployment information');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): mixed
    {
        $writer = $this->io()->writer();
        $writer->boldGreen('Deployment information', true)->eol();

        $writer->bold('OS: ');
        $writer->boldBlueBgBlack(PHP_OS, true);

        $writer->bold('Architecture: ');
        $writer->boldBlueBgBlack(php_uname('m'), true);

        $writer->bold('PHP Version: ');
        $writer->boldBlueBgBlack(PHP_VERSION, true);

        $writer->bold('Platine Version: ');
        $writer->boldBlueBgBlack($this->application->version(), true);

        $writer->bold('Platine Environment: ');
        $writer->boldBlueBgBlack($this->application->getEnvironment(), true);

        $writer->bold('Debug mode: ');
        $writer->boldBlueBgBlack($this->config->get('app.debug', false) ? 'On' : 'Off', true);

        $packages = $this->getPackages();

        $writer->bold(sprintf('Installed Packages: (%d)', count($packages)), true);
        /** @var array<int, array<int, array<string, string>>> $rows*/
        $rows = [];
        foreach ($packages as $package) {
            $rows[] = [
                'name' => $package['name'],
                'description' => Str::limit($package['description'], 60),
                'version' => $package['version'],
                'type' => $package['type'],
                'dev' => $package['dev'] ? 'Yes' : 'No',
            ];
        }
        $writer->table($rows);

        $writer->green('Command finished successfully')->eol();

        return true;
    }

    /**
     * Return the composer packages
     * @return array<int, array<string, mixed>>
     */
    protected function getPackages(): array
    {
        $packages = Composer::parseLockFile($this->application->getRootPath());
        Arr::multisort($packages, 'dev');

        return $packages;
    }
}
