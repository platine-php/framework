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
 *  @file Permission.php
 *
 *  The Permission Entity class
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

use Platine\Orm\Entity;
use Platine\Orm\Mapper\EntityMapperInterface;
use Platine\Orm\Query\Query;
use Platine\Orm\Relation\ForeignKey;

/**
 * @class Permission
 * @package Platine\Framework\Auth\Entity
 * @extends Entity<Permission>
 */
class Permission extends Entity
{
    /**
     *
     * @param EntityMapperInterface<Permission> $mapper
     * @return void
     */
    public static function mapEntity(EntityMapperInterface $mapper): void
    {
        $mapper->useTimestamp();
        $mapper->casts([
            'created_at' => 'date',
            'updated_at' => '?date',
        ]);

        $mapper->relation('roles')->shareMany(Role::class);
        $mapper->relation('parent')->belongsTo(Permission::class, new ForeignKey([
            'id' => 'parent_id'
        ]));


        $mapper->filter('parent', function (Query $q, $value) {
             $q->where('parent_id')->is($value);
        });
    }
}
