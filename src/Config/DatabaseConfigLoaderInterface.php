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
 *  @file DatabaseConfigLoaderInterface.php
 *
 *  The Database Configuration loader interface
 *
 *  @package    Platine\Framework\Config
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Config;

use Platine\Orm\Entity;

/**
 * @class DatabaseConfigLoaderInterface
 * @package Platine\Framework\Config
 * @template TDbConfigurationEntity as Entity
 */
interface DatabaseConfigLoaderInterface
{
    /**
     * Load the configuration group for the given environment with custom filters
     * @param  string $environment   the name of the environment
     * like "dev", "prod", etc
     * @param  string $group the group name to load
     * @param  array<string, mixed>  $filters the filters to use if any
     * @return array<string, mixed>        the loaded configuration items
     */
    public function load(string $environment, string $group, array $filters = []): array;

    /**
     * Load the configuration from database
     * @param array<string, mixed> $where
     * @return TDbConfigurationEntity|null
     */
    public function loadConfig(array $where = []): ?Entity;

    /**
     * Insert new configuration
     * @param array<string, mixed> $data
     * @return mixed
     */
    public function insertConfig(array $data): mixed;

    /**
     * Update the configuration
     * @param TDbConfigurationEntity $entity
     * @return bool
     */
    public function updateConfig(Entity $entity): bool;

    /**
     * Return all the configuration
     * @return TDbConfigurationEntity[]
     */
    public function all(): array;
}
