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
 *  @file MaintenanceCommand.php
 *
 *  The Maintenance management command class
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
use RuntimeException;
use Throwable;

/**
 * @class MaintenanceCommand
 * @package Platine\Framework\Console\Command
 * @template T
 */
class MaintenanceCommand extends Command
{
    /**
     * The configuration to use
     * @var Config<T>
     */
    protected Config $config;

    /**
     * The Platine Application
     * @var Application
     */
    protected Application $application;

    /**
     * Create new instance
     * @param Application $app
     * @param Config<T> $config
     */
    public function __construct(
        Application $app,
        Config $config
    ) {
        $this->application = $app;
        $this->config = $config;

        $this->setName('maintenance')
             ->setDescription('Command to manage application maintenance');

        $this->addArgument('type', 'type of action [up|down|status]', 'status', true, true, false, function ($val) {
            if (!in_array($val, ['up', 'down', 'status'])) {
                throw new RuntimeException(sprintf(
                    'Invalid argument type [%s], must be one of [up, down, status]',
                    $val
                ));
            }

             return $val;
        });

        $this->addOption(
            '-t|--template',
            'The template that should be rendered for display during maintenance mode',
            null,
            false,
            true
        );

        $this->addOption(
            '-r|--retry',
            'The number of seconds after which the request may be retried',
            3600,
            false,
            true,
            false,
            function ($val) {
                if (strlen($val) > 0 && (!is_numeric($val) || (int) $val <= 0)) {
                    throw new RuntimeException(sprintf(
                        'Invalid retry value [%s], must be an integer greather than zero',
                        $val
                    ));
                }

                return $val;
            }
        );
        $this->addOption(
            '-e|--refresh',
            'The number of seconds after which the browser may refresh',
            3600,
            false,
            true,
            false,
            function ($val) {
                if (strlen($val) > 0 && (!is_numeric($val) || (int) $val <= 0)) {
                    throw new RuntimeException(sprintf(
                        'Invalid refresh value [%s], must be an integer greather than zero',
                        $val
                    ));
                }

                return $val;
            }
        );
        $this->addOption(
            '-s|--secret',
            'The secret phrase that may be used to bypass maintenance mode',
            null,
            false,
            true
        );
        $this->addOption(
            '-c|--status',
            'The status code that should be used when returning the maintenance mode response',
            503,
            false,
            true,
            false,
            function ($val) {
                if (strlen($val) > 0 && (!is_numeric($val) || (int) $val < 200 || (int) $val > 505)) {
                    throw new RuntimeException(sprintf(
                        'Invalid HTTP status value [%s], must be between 200 and 505',
                        $val
                    ));
                }

                return $val;
            }
        );
        $this->addOption(
            '-m|--message',
            'The message that will be shown to user during maintenance mode',
            null,
            false,
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $type = $this->getArgumentValue('type');

        $io = $this->io();
        $writer = $io->writer();
        $writer->boldYellow('APPLICATION MAINTENANCE MANAGEMENT', true)->eol();

        if ($type === 'up') {
            $this->online();
        } elseif ($type === 'down') {
            $this->down();
        } else {
            $this->status();
        }
    }

    /**
     * Put application online
     * @return void
     */
    public function online(): void
    {
        $writer = $this->io()->writer();

        try {
            if ($this->application->isInMaintenance() === false) {
                $writer->boldRed('Application already online')->eol();
                return;
            }

            $this->application->maintenance()->deactivate();

            $writer->boldGreen('Application is now online')->eol();
        } catch (Throwable $ex) {
            $writer->boldRed(sprintf(
                'Failed to disable maintenance mode: %s.',
                $ex->getMessage()
            ))->eol();
        }
    }

    /**
     * Put application in maintenance mode
     * @return void
     */
    public function down(): void
    {
        $writer = $this->io()->writer();

        try {
            if ($this->application->isInMaintenance()) {
                $writer->boldRed('Application is already down.')->eol();
                return;
            }

            $data = $this->getPayload();
            $this->application->maintenance()->activate($data);

            $writer->boldGreen('Application is now in maintenance mode.')->eol();
        } catch (Throwable $ex) {
            $writer->boldRed(sprintf(
                'Failed to enable maintenance mode: %s.',
                $ex->getMessage()
            ))->eol();
        }
    }

    /**
     * Check application maintenance mode
     * @return void
     */
    public function status(): void
    {
        $writer = $this->io()->writer();

        if ($this->application->isInMaintenance()) {
            $writer->boldYellow('Application is down.')->eol();
        } else {
            $writer->boldGreen('Application is online.')->eol();
        }
    }

    /**
     * Get the payload to be placed in the maintenance file.
     * @return array<string, mixed>
     */
    protected function getPayload(): array
    {
        $retry = $this->getOptionValue('retry') ?? 3600;
        if ($retry) {
            $retry = (int) $retry;
        }

        $refresh = $this->getOptionValue('refresh') ?? 3600;
        if ($refresh) {
            $refresh = (int) $refresh;
        }

        $status = $this->getOptionValue('status') ?? 503;
        if ($status) {
            $status = (int) $status;
        }

        return [
            'except' => $this->config->get('maintenance.url_whitelist', []),
            'template' => $this->getOptionValue('template'),
            'retry' => $retry,
            'refresh' => $refresh,
            'secret' => $this->getOptionValue('secret'),
            'status' => $status,
            'message' => $this->getOptionValue('message'),
        ];
    }
}
