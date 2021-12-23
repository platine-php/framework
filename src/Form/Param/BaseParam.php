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
 *  @file BaseParam.php
 *
 *  The Form base parameter class
 *
 *  @package    Platine\Framework\Form\Param
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Form\Param;

use Platine\Orm\Entity;
use Platine\Stdlib\Helper\Str;

/**
 * @class BaseParam
 * @package Platine\Framework\Form\Param
 */
class BaseParam
{
    /**
     * Create new instance
     * @param array<string, mixed> $data
     */
    public function __construct(array $data = [])
    {
        $params = array_merge($this->getDefault(), $data);
        $this->load($params);
    }

    /**
     * Load the field data
     * @param array<string, mixed> $data
     * @return void
     */
    public function load(array $data): void
    {
        foreach ($data as $name => $value) {
            $key = Str::camel($name, true);

            $setterMethod = 'set' . ucfirst($key);
            if (method_exists($this, $setterMethod)) {
                $this->{$setterMethod}($value);
            } elseif (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     *
     * @param Entity $entity
     * @return $this
     */
    public function fromEntity(Entity $entity): self
    {
        return $this;
    }

    /**
     * Return the fields default values
     * @return array<string, mixed>
     */
    public function getDefault(): array
    {
        return [];
    }

    /**
     * Return the value for the given property
     * @param string $name
     * @return mixed|null
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        //convert to camel
        $key = Str::camel($name, true);
        if (property_exists($this, $key)) {
            return $this->{$key};
        }

        return null;
    }
}
