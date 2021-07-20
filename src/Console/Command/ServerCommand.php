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
 *  @file ServerCommand.php
 *
 *  The Server command class
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

use Platine\Console\Command\Command;
use Platine\Console\Command\Shell;

/**
 * class ServerCommand
 * @package Platine\Framework\Console\Command
 */
class ServerCommand extends Command
{

    /**
     * Create new instance
     * {@inheritodc}
     */
    public function __construct()
    {
        parent::__construct('server', 'Command to manage PHP development server');
        $this->addOption('-a|--address', 'Server address', '0.0.0.0', true);
        $this->addOption('-p|--port', 'Server listen port', '8080', true);
        $this->addOption('-r|--root', 'Server listen port', 'public', true);
    }

    /**
     * {@inheritodc}
     */
    public function execute()
    {
        $host = $this->getOptionValue('address');
        $port = $this->getOptionValue('port');
        $path = $this->getOptionValue('root');
        $cmd = sprintf('php -S %s:%s -t %s', $host, $port, $path);
        $writer = $this->io()->writer();
        $writer->boldYellow(sprintf('Running command [%s]', $cmd), true);
        $shell = new Shell($cmd);

        $shell->setOptions(null, $_ENV, null, ['bypass_shell' => true]);

        $shell->execute();
        $writer->boldWhite($shell->getOutput(), true);
        if ($shell->getExitCode() !== 0) {
            $writer->boldRed($shell->getErrorOutput(), true);
        }
    }
}
