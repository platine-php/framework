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
 *  @file Audit.php
 *
 *  The Audit Entity class
 *
 *  @package    Platine\Framework\Audit\Model
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Audit\Model;

use Platine\Framework\Auth\Entity\User;
use Platine\Orm\Entity;
use Platine\Orm\Mapper\EntityMapperInterface;
use Platine\Orm\Query\Query;

/**
 * @class Audit
 * @package Platine\Framework\Audit\Model
 */
class Audit extends Entity
{
    /**
     * {@inheritdoc}
     */
    public static function mapEntity(EntityMapperInterface $mapper): void
    {
        $mapper->relation('user')->belongsTo(User::class);

        $mapper->casts([
            'date' => 'date',
        ]);

        $mapper->filter('start_date', function (Query $q, $value) {
            $q->where('date')->gte($value);
        });

        $mapper->filter('end_date', function (Query $q, $value) {
            $q->where('date')->lte($value);
        });

        $mapper->filter('event', function (Query $q, $value) {
            $q->where('event')->is($value);
        });

        $mapper->filter('user', function (Query $q, $value) {
            $q->where('user_id')->is($value);
        });
    }
}
