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
use Platine\Framework\Auth\Enum\UserStatus;
use Platine\Framework\Auth\Event\AuthInvalidPasswordEvent;
use Platine\Framework\Auth\Event\AuthLoginEvent;
use Platine\Framework\Auth\Exception\AccountLockedException;
use Platine\Framework\Auth\Exception\AccountNotFoundException;
use Platine\Framework\Auth\Exception\InvalidCredentialsException;
use Platine\Framework\Auth\Exception\MissingCredentialsException;
use Platine\Framework\Auth\IdentityInterface;
use Platine\Framework\Auth\Repository\UserRepository;
use Platine\Security\Hash\HashInterface;
use Platine\Session\Session;

/**
 * class SessionAuthentication
 * @package Platine\Framework\Auth\Authentication
 */
class SessionAuthentication implements AuthenticationInterface
{
    /**
     * Create new instance
     * @param Application $app
     * @param HashInterface $hash
     * @param Session $session
     * @param UserRepository $userRepository
     */
    public function __construct(
        protected Application $app,
        protected HashInterface $hash,
        protected Session $session,
        protected UserRepository $userRepository
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getUser(): IdentityInterface
    {
        if ($this->isLogged() === false) {
            throw new AccountNotFoundException('User not logged', 401);
        }

        $id = $this->session->get('user.id');
        $user = $this->userRepository->find($id);

        if ($user === null) {
            throw new AccountNotFoundException(
                'Can not find the logged user information, may be data is corrupted',
                401
            );
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function isLogged(): bool
    {
        return $this->session->has('user');
    }

    /**
     * {@inheritdoc}
     */
    public function login(
        array $credentials = [],
        bool $remeberMe = false,
        bool $withPassword = true
    ): bool {
        if (!isset($credentials['username'])) {
            throw new MissingCredentialsException(
                'Missing username information',
                401
            );
        }

        if ($withPassword && !isset($credentials['password'])) {
            throw new MissingCredentialsException(
                'Missing password information',
                401
            );
        }

        $username = $credentials['username'];
        $password = $credentials['password'] ?? '';

        $user = $this->getUserEntity($username, $password, $withPassword);
        if ($user === null) {
            throw new AccountNotFoundException(
                sprintf(
                    'Can not find the user [%s]',
                    $username
                ),
                401
            );
        } elseif ($user->status === UserStatus::LOCKED) {
            throw new AccountLockedException(
                sprintf('User [%s] is locked', $username),
                401
            );
        }

        if ($withPassword && $this->hash->verify($password, $user->password) === false) {
            $this->app->dispatch(new AuthInvalidPasswordEvent($user));

            throw new InvalidCredentialsException(
                sprintf('Invalid credentials for user [%s]', $username),
                401
            );
        }

        $permissions = [];
        $roles = $user->roles;
        foreach ($roles as $role) {
            $rolePermissions = $role->permissions;
            foreach ($rolePermissions as $permission) {
                $permissions[] = $permission->code;
            }
        }

        $data = [
          'id' => $user->id,
          'username' => $user->username,
          'lastname' => $user->lastname,
          'firstname' => $user->firstname,
          'permissions' => array_unique($permissions),
        ];

        $this->session->set('user', array_merge($data, $this->getUserData($user)));

        // Inform the system that the use just login successfully
        $this->app->dispatch(new AuthLoginEvent($user));

        return $this->isLogged();
    }

    /**
     * {@inheritdoc}
     */
    public function logout(bool $destroy = true): void
    {
        $this->session->remove('user');

        if ($destroy) {
            $params = session_get_cookie_params();
            setcookie(
                (string) session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
            session_unset();
            session_destroy();
        }
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
}
