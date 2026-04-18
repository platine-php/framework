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
 *  @file SessionAuthentication.php
 *
 *  The Authentication using session feature class
 *
 *  @package    Platine\Framework\Auth\Authentication
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Auth\Authentication;

use Platine\Framework\App\Application;
use Platine\Framework\Auth\AuthenticationInterface;
use Platine\Framework\Auth\Entity\User;
use Platine\Framework\Auth\Repository\UserRepository;
use Platine\Security\Hash\HashInterface;

/**
 * class BaseAuthentication
 * @package Platine\Framework\Auth\Authentication
 */
abstract class BaseAuthentication implements AuthenticationInterface
{
    /**
     * Create new instance
     * @param HashInterface $hash
     * @param UserRepository $userRepository
     * @param Application $app
     */
    public function __construct(
        protected HashInterface $hash,
        protected UserRepository $userRepository,
        protected Application $app,
    ) {
    }

    /**
     * Return the login data
     * @param User $user
     * @return array<string, mixed>
     */
    protected function getLoginData(User $user): array
    {
        $permissions = $this->getUserPermissions($user);
        $data = [
          'user' => [
            'id' => $user->id,
            'username' => $user->username,
            'lastname' => $user->lastname,
            'firstname' => $user->firstname,
            'email' => $user->email,
            'status' => $user->status,
          ],
          'permissions' => $permissions,
        ];

        return array_merge($data, $this->getUserData($user));
    }

    /**
     * Return the user entity
     * @param string $username
     * @param string $password
     * @param bool $withPassword wether to use password to login
     * @return User|null
     */
    protected function getUserEntity(
        string $username,
        string $password,
        bool $withPassword = true
    ): ?User {
        return $this->userRepository->with('roles.permissions')
                                    ->findBy(['username' => $username]);
    }

    /**
     * Return the user additional data
     * @param User $user
     * @return array<string, mixed>
     */
    protected function getUserData(User $user): array
    {
        return [];
    }

    /**
     * Return the user additional attribute to be saved along with login information
     * @param User $user
     * @return array<string, mixed>
     */
    protected function getUserAttribute(User $user): array
    {
        return [];
    }

    /**
     * Return the permission list of the given user
     * @param User|int $user
     * @return string[]
     */
    protected function getUserPermissions(User|int $user): array
    {
        $permissions = [];
        if (is_int($user)) {
            $user = $this->userRepository->with('roles.permissions')
                                         ->find($user);
            if ($user === null) {
                return [];
            }
        }

        $roles = $user->roles;
        foreach ($roles as $role) {
            $rolePermissions = $role->permissions;
            foreach ($rolePermissions as $permission) {
                if (in_array($permission->code, $permissions) === false) {
                    $permissions[] = $permission->code;
                }
            }
        }

        return $permissions;
    }
}
