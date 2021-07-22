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
 *  @file RoleParam.php
 *
 *  The Role form parameter class
 *
 *  @package    Platine\Framework\Demo\Form\Param
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Demo\Form\Param;

use Platine\Framework\Form\Param\BaseParam;
use Platine\Orm\Entity;

/**
 * @class RoleParam
 * @package Platine\Framework\Demo\Form\Param
 */
class RoleParam extends BaseParam
{
    /**
     * The name
     * @var string
     */
    protected string $name = '';

    /**
     * The description
     * @var string
     */
    protected string $description = '';

    /**
     * The selected permissions id
     * @var int[]
     */
    protected array $permissions = [];

    /**
     * {@inheritodc}
     */
    public function fromEntity(Entity $entity): self
    {
        $this->name = $entity->name;
        $this->description = (string) $entity->description;

        return $this;
    }

    /**
     * Return the name
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Return the description
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Return the permissions
     * @return int[]
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * Set the name
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set the description
     * @param string $description
     * @return $this
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Set the permissions
     * @param int[] $permissions
     * @return $this
     */
    public function setPermissions(array $permissions): self
    {
        $this->permissions = $permissions;
        return $this;
    }
}
