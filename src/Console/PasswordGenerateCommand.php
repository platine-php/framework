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
 *  @file PasswordGenerateCommand.php
 *
 *  The Password Generator Command class
 *
 *  @package    Platine\Framework\Console
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Console;

use Platine\Console\Command\Command;
use Platine\Console\Input\Reader;
use Platine\Console\Output\Writer;
use Platine\Security\Hash\HashInterface;

/**
 * @class PasswordGenerateCommand
 * @package Platine\Framework\Console
 */
class PasswordGenerateCommand extends Command
{
    /**
     * The Hash instance
     * @var HashInterface
     */
    protected HashInterface $hash;

    /**
     * The plain password to use
     * @var string
     */
    protected string $password = '';

    /**
     * Create new instance
     * @param HashInterface $hash
     */
    public function __construct(
        HashInterface $hash
    ) {
        parent::__construct('password:generate', 'Command to generate password');
        $this->hash = $hash;
        $this->addArgument('password', 'The password to hash', null, false);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $io = $this->io();
        $writer = $io->writer();
        $password = $this->password;
        $passwordHash = $this->hash->hash($password);

        $writer->boldGreen(sprintf(
            'Plain password: [%s]',
            $password
        ), true);

        $writer->boldGreen(sprintf('Hashed password: %s', $passwordHash), true)->eol();

        $writer->green('Command finished successfully')->eol();
    }

    /**
     * {@inheritdoc}
     */
    public function interact(Reader $reader, Writer $writer): void
    {
        $writer->boldYellow('GENERATION OF PASSWORD', true)->eol();
        $password = $this->getArgumentValue('password');
        if (empty($password)) {
            $io = $this->io();
            $password = $io->prompt('Enter the plain password to generate', '');
        }

        $this->password = $password;
    }
}
