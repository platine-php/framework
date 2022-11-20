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
 *  @file CommandServiceProvider.php
 *
 *  The Framework command service provider class
 *
 *  @package    Platine\Framework\Service\Provider
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Service\Provider;

use Platine\Framework\Console\Command\ConfigCommand;
use Platine\Framework\Console\Command\MakeActionCommand;
use Platine\Framework\Console\Command\MakeEntityCommand;
use Platine\Framework\Console\Command\MakeEventCommand;
use Platine\Framework\Console\Command\MakeFormParamCommand;
use Platine\Framework\Console\Command\MakeListenerCommand;
use Platine\Framework\Console\Command\MakeMiddlewareCommand;
use Platine\Framework\Console\Command\MakeProviderCommand;
use Platine\Framework\Console\Command\MakeRepositoryCommand;
use Platine\Framework\Console\Command\MakeTaskCommand;
use Platine\Framework\Console\Command\MakeValidatorCommand;
use Platine\Framework\Console\Command\RouteCommand;
use Platine\Framework\Console\Command\VendorPublishCommand;
use Platine\Framework\Console\PasswordGenerateCommand;
use Platine\Framework\Service\ServiceProvider;

/**
 * @class CommandServiceProvider
 * @package Platine\Framework\Service\Provider
 */
class CommandServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $this->app->bind(RouteCommand::class);
        $this->app->bind(ConfigCommand::class);
        $this->app->bind(VendorPublishCommand::class);
        $this->app->bind(MakeActionCommand::class);
        $this->app->bind(MakeEntityCommand::class);
        $this->app->bind(MakeRepositoryCommand::class);
        $this->app->bind(MakeFormParamCommand::class);
        $this->app->bind(MakeValidatorCommand::class);
        $this->app->bind(MakeProviderCommand::class);
        $this->app->bind(MakeMiddlewareCommand::class);
        $this->app->bind(MakeEventCommand::class);
        $this->app->bind(MakeListenerCommand::class);
        $this->app->bind(MakeTaskCommand::class);
        $this->app->bind(PasswordGenerateCommand::class);

        //Commands
        $this->addCommand(VendorPublishCommand::class);
        $this->addCommand(MakeActionCommand::class);
        $this->addCommand(MakeEntityCommand::class);
        $this->addCommand(MakeRepositoryCommand::class);
        $this->addCommand(MakeFormParamCommand::class);
        $this->addCommand(MakeValidatorCommand::class);
        $this->addCommand(MakeProviderCommand::class);
        $this->addCommand(MakeMiddlewareCommand::class);
        $this->addCommand(MakeEventCommand::class);
        $this->addCommand(MakeListenerCommand::class);
        $this->addCommand(MakeTaskCommand::class);
        $this->addCommand(PasswordGenerateCommand::class);
    }
}
