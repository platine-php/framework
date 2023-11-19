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
 *  @file FileMaintenanceDriver.php
 *
 *  The Maintenance driver based on file system
 *
 *  @package    Platine\Framework\Http\Maintenance\Driver
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Http\Maintenance;

use Platine\Config\Config;
use Platine\Filesystem\Filesystem;
use Platine\Stdlib\Helper\Json;
/**
 * @class FileMaintenanceDriver
 * @package Platine\Framework\Http\Maintenance\Driver
 * @template T
 */
class FileMaintenanceDriver implements MaintenanceDriverInterface
{
    /**
     * The application configuration
     * @var Config<T>
     */
    protected Config $config;

    /**
     * The file system instance
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * Create new instance
     * @param Config<T> $config
     * @param Filesystem $filesystem
     */
    public function __construct(Config $config, Filesystem $filesystem)
    {
        $this->config = $config;
        $this->filesystem = $filesystem;
    }


    /**
     * {@inheritdoc}
     */
    public function active(): bool
    {
        $path = $this->getFilePath();

        return $this->filesystem->file($path)->exists();
    }

    /**
     * {@inheritdoc}
     */
    public function activate(array $data): void
    {
        $json = Json::encode($data, JSON_PRETTY_PRINT);
        $path = $this->getFilePath();
        $this->filesystem->file($path)->write($json);
    }

    /**
     * {@inheritdoc}
     */
    public function deactivate(): void
    {
        if ($this->active()) {
            $path = $this->getFilePath();
            $this->filesystem->file($path)->delete();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function data(): array
    {
        $path = $this->getFilePath();
        $json = $this->filesystem->file($path)->read();

        return Json::decode($json, true);
    }

    /**
     * Return the maintenance file path
     * @return string
     */
    protected function getFilePath(): string
    {
        return $this->config->get('maintenance.storages.file.path', '');
    }
}
