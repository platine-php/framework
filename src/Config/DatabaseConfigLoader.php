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
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Config;

use Platine\Framework\Config\Model\DatabaseConfigRepository;
use Platine\Orm\Entity;

/**
 * @class DatabaseConfigLoader
 * @package Platine\Framework\Config
 */
class DatabaseConfigLoader implements DatabaseConfigLoaderInterface
{

    /**
     * The Repository instance
     * @var DatabaseConfigRepository
     */
    protected DatabaseConfigRepository $repository;


    /**
     * Create new instance
     * @param DatabaseConfigRepository $repository
     */
    public function __construct(DatabaseConfigRepository $repository)
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
    public function loadConfig(array $where = []): ?Entity
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
    public function updateConfig(Entity $entity): bool
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
        $query = $this->repository
                                ->query()
                                ->where('module')->is($group)
                                ->where('status')->is(1);
        if ($env === null) {
            $query->where('env')->isNull();
        } else {
            $query->where('env')->is($env);
        }

        $results = $query->all();

        $items = [];
        foreach ($results as $result) {
            if (!empty($result->name)) {
                $items[$result->name] = $this->convertToDataType(
                    $result->value,
                    (string) $result->type
                );
            }
        }

        return $items;
    }

    /**
     * Convert the returned configuration to given type
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    protected function convertToDataType($value, string $type)
    {
        switch ($type) {
            case 'integer':
                $value = intval($value);
                break;
            case 'double':
                $value = doubleval($value);
                break;
            case 'float':
                $value = floatval($value);
                break;
            case 'array':
            case 'object':
                $value = unserialize($value);
                break;
            case 'boolean':
                $value = boolval($value);
                break;
        }

        return $value;
    }
}
