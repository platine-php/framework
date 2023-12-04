<?php

/**
 * Platine Framework
 *
 * Platine Framework is a lightweight, high-performance, simple and elegant PHP
 * Web framework
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
 *  @file MaintenanceDriverInterface.php
 *
 *  The Maintenance driver interface
 *
 *  @package    Platine\Framework\Http\Maintenance
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Http\Maintenance;

/**
 * @class MaintenanceDriverInterface
 * @package Platine\Framework\Http\Maintenance
 */
interface MaintenanceDriverInterface
{
    /**
     * Whether the maintenance is active or not
     * @return bool
     */
    public function active(): bool;

    /**
     * Put the application in maintenance
     *
     * @param array<string, mixed> $data the maintenance payload
     * The array keys are:
     *      except: The path that users should be redirected to
     *      template: The template that should be rendered for display during maintenance mode
     *      retry: The number of seconds after which the request may be retried
     *      refresh: The number of seconds after which the browser may refresh
     *      secret: The secret phrase that may be used to bypass maintenance mode
     *      status: The status code that should be used when returning the maintenance mode response
     *      message: The message that will be shown to user during maintenance mode
     *
     *  @return void
     */
    public function activate(array $data): void;

    /**
     * Move the application out of maintenance
     * @return void
     */
    public function deactivate(): void;

    /**
     * Get the data which was provided when the application
     * was placed into maintenance.
     * @return array<string, mixed>
     */
    public function data(): array;
}
