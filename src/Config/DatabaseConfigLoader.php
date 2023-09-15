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
 *  @file DatabaseConfigLoader.php
 *
 *  The Database Configuration loader class
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

use Platine\Database\Query\WhereStatement;
use Platine\Framework\Config\Model\Configuration;
use Platine\Framework\Config\Model\ConfigurationRepository;

/**
 * @class DatabaseConfigLoader
 * @package Platine\Framework\Config
 */
class DatabaseConfigLoader implements DatabaseConfigLoaderInterface
{
    /**
     * The Repository instance
     * @var ConfigurationRepository
     */
    protected ConfigurationRepository $repository;


    /**
     * Create new instance
     * @param ConfigurationRepository $repository
     */
    public function __construct(ConfigurationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $environment, string $group): array
    {
        return $this->loadDbConfigurations($group, $environment);
    }

    /**
     * {@inheritdoc}
     */
    public function loadConfig(array $where = []): ?Configuration
    {
        return $this->repository->findBy($where);
    }

    /**
     * {@inheritdoc}
     */
    public function insertConfig(array $data)
    {
        $entity = $this->repository->create($data);
        return $this->repository->insert($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function updateConfig(Configuration $entity): bool
    {
        return $this->repository->save($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        return $this->repository->all();
    }

    /**
     * Return the configuration
     * @param string $group
     * @param string|null $env
     * @return array<string, mixed>
     */
    protected function loadDbConfigurations(string $group, ?string $env = null): array
    {
        // @codeCoverageIgnoreStart
        $query = $this->repository
                                ->query()
                                ->where('module')->is($group)
                                ->where('status')->is('Y')
                                ->where(function (WhereStatement $where) use ($env) {
                                    $where->where('env')->is($env)
                                    ->orWhere('env')->isNull();
                                });
        // @codeCoverageIgnoreEnd

        /** @var Configuration[] $results */
        $results = $query->all();

        $items = [];
        foreach ($results as $result) {
            /** @var string $name */
            $name = $result->name;
            if (!empty($name)) {
                $items[$name] = ConfigUtil::convertToDataType(
                    $result->value,
                    (string) $result->type
                );
            }
        }

        return $items;
    }
}
