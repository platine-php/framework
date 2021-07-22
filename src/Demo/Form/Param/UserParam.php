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
 *  @file UserParam.php
 *
 *  The User form parameter class
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
 * @class UserParam
 * @package Platine\Framework\Demo\Form\Param
 */
class UserParam extends BaseParam
{
    /**
     * The username
     * @var string
     */
    protected string $username = '';

    /**
     * The last name
     * @var string
     */
    protected string $lastname = '';

    /**
     * The email
     * @var string
     */
    protected string $email = '';

    /**
     * The status
     * @var string
     */
    protected string $status = '';

    /**
     * The first name
     * @var string
     */
    protected string $firstname = '';

    /**
     * The password
     * @var string
     */
    protected string $password = '';

    /**
     * The function or role
     * @var string
     */
    protected string $role = '';

    /**
     * The selected roles id
     * @var int[]
     */
    protected array $roles = [];

    /**
     * {@inheritodc}
     */
    public function fromEntity(Entity $entity): self
    {
        $this->username = $entity->username;
        $this->lastname = $entity->lastname;
        $this->firstname = $entity->firstname;
        $this->password = $entity->password;
        $this->role = (string) $entity->role;
        $this->email = $entity->email;
        $this->status = (string) $entity->status;

        return $this;
    }

    /**
     * Return the roles id
     * @return int[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * Set the user roles
     * @param int[] $roles
     * @return $this
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * Return the user email
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Return the user status
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Set the user email
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Set the user status
     * @param string $status
     * @return $this
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Return the username
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Set the username
     * @param string $username
     * @return $this
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Return the password
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Set the password
     * @param string $password
     * @return $this
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Return the last name
     * @return string
     */
    public function getLastname(): string
    {
        return $this->lastname;
    }

    /**
     * Return the first name
     * @return string
     */
    public function getFirstname(): string
    {
        return $this->firstname;
    }

    /**
     * Return the role or function
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * Set the last name
     * @param string $lastname
     * @return $this
     */
    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;
        return $this;
    }

    /**
     * Set the first name
     * @param string $firstname
     * @return $this
     */
    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;
        return $this;
    }

    /**
     * Set the user role or function
     * @param string $role
     * @return $this
     */
    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }
}
