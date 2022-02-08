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
 *  @file ConfigCommand.php
 *
 *  The Configuration management command class
 *
 *  @package    Platine\Framework\Console\Command
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Console\Command;

use Platine\Config\Config;
use Platine\Console\Command\Command;
use Platine\Stdlib\Helper\Str;

/**
 * @class ConfigCommand
 * @package Platine\Framework\Console\Command
 * @template T
 */
class ConfigCommand extends Command
{
    /**
     * The configuration instance
     * @var Config<T>
     */
    protected Config $config;

    /**
     * Create new instance
     * @param Config<T> $config
     */
    public function __construct(Config $config)
    {
        parent::__construct('config', 'Command to manage configuration');

        $this->addOption('-l|--list', 'List the configuration', '', false);
        $this->addOption('-t|--type', 'Configuration type', 'app', true);

        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if ($this->getOptionValue('list')) {
            $this->showConfigList();
        }
    }

    /**
     * Show the configuration list
     * @return void
     */
    protected function showConfigList(): void
    {
        $writer = $this->io()->writer();
        $type = $this->getOptionValue('type');

        $writer->boldGreen(sprintf('Show configuration for [%s]', $type), true)->eol();

        $items = (array) $this->config->get($type, []);
        /** @var array<int, array<int, array<string, string>>> $rows*/
        $rows = [];
        foreach ($items as $name => $value) {
            $valueStr = Str::stringify($value);
            if (is_int($name)) {
                $rows[] = [
                    'value' => $valueStr
                ];
            } else {
                $rows[] = [
                    'name' => $name,
                    'value' => $valueStr
                ];
            }
        }

        $writer->table($rows);

        $writer->green('Command finished successfully')->eol();
    }
}
