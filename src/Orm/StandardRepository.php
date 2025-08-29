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

declare(strict_types=1);

namespace Platine\Framework\Orm;

use Platine\Orm\EntityManager;
use Platine\Orm\Mapper\EntityMapper;
use Platine\Orm\Repository;

/**
* @class StandardRepository
* @package Platine\Framework\Orm
*/
class StandardRepository extends Repository
{
    /**
    * Create new instance
    * @param EntityManager $manager
    */
    public function __construct(EntityManager $manager)
    {
        parent::__construct($manager, StandardEntity::class);
    }

    /**
     * Return entity mapper instance
     * @return EntityMapper
     */
    public function getEntityMapper(): EntityMapper
    {
        return $this->manager->getEntityMapper($this->entityClass);
    }

    /**
     * Set the table name to be used
     * @param string $name
     * @return $this
     */
    public function setTable(string $name): self
    {
        $this->getEntityMapper()->table($name);

        return $this;
    }

    /**
     * Add query filter
     * @param string $name
     * @param callable $filter
     * @return $this
     */
    public function addFilter(string $name, callable $filter): self
    {
        $this->getEntityMapper()->filter($name, $filter);

        return $this;
    }
}
