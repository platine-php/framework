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
 *  @file User.php
 *
 *  The User Entity class
 *
 *  @package    Platine\Framework\Auth\Entity
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Auth\Entity;

use Platine\Framework\Auth\IdentityInterface;
use Platine\Orm\Entity;
use Platine\Orm\Mapper\EntityMapperInterface;
use Platine\Orm\Query\Query;

/**
 * @class User
 * @package Platine\Framework\Auth\Entity
 */
class User extends Entity implements IdentityInterface
{
    /**
     * {@inheritdoc}
     */
    public static function mapEntity(EntityMapperInterface $mapper): void
    {
        $mapper->relation('roles')->shareMany(Role::class);
        $mapper->useTimestamp();
        $mapper->casts([
            'created_at' => 'date',
            'updated_at' => '?date',
        ]);

        $mapper->filter('status', function (Query $q, $status) {
            $q->where('status')->is($status);
        });
    }

    /**
     * Set roles
     * @param Role[] $roles
     * @return $this
     */
    public function setRoles(array $roles): self
    {
        foreach ($roles as $role) {
            $this->mapper()->link('roles', $role);
        }

        return $this;
    }

    /**
     * Remove roles
     * @param Role[] $roles
     * @return $this
     */
    public function removeRoles(array $roles): self
    {
        foreach ($roles as $role) {
            $this->mapper()->unlink('roles', $role);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->mapper()->getColumn('id');
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return sprintf(
            '%s %s',
            $this->mapper()->getColumn('firstname'),
            $this->mapper()->getColumn('lastname')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername(): string
    {
        return $this->mapper()->getColumn('username');
    }
}
