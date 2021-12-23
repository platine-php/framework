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
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Auth\Authentication;

use Platine\Framework\App\Application;
use Platine\Framework\Auth\AuthenticationInterface;
use Platine\Framework\Auth\Event\AuthInvalidPasswordEvent;
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
     * The session instance to use
     * @var Session
     */
    protected Session $session;

    /**
     * The user repository instance
     * @var UserRepository
     */
    protected UserRepository $userRepository;

    /**
     * Hash instance to use
     * @var HashInterface
     */
    protected HashInterface $hash;

    /**
     * The application instance
     * @var Application
     */
    protected Application $app;

    /**
     * Create new instance
     * @param Application $app
     * @param HashInterface $hash
     * @param Session $session
     * @param UserRepository $userRepository
     */
    public function __construct(
        Application $app,
        HashInterface $hash,
        Session $session,
        UserRepository $userRepository
    ) {
        $this->app = $app;
        $this->hash = $hash;
        $this->session = $session;
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser(): IdentityInterface
    {
        if (!$this->isLogged()) {
            throw new AccountNotFoundException('User not logged', 401);
        }

        $id = $this->session->get('user.id');
        $user = $this->userRepository->find($id);

        if (!$user) {
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
    public function login(array $credentials = [], bool $remeberMe = false): bool
    {
        if (!isset($credentials['username']) || !isset($credentials['password'])) {
            throw new MissingCredentialsException(
                'Missing username or password information',
                401
            );
        }

        $username = $credentials['username'];
        $password = $credentials['password'];
        $user = $this->userRepository
                    ->with('roles.permissions')
                    ->findBy(['username' => $username]);
        if (!$user) {
            throw new AccountNotFoundException('Can not find the user with the given information', 401);
        } elseif ($user->status === 'D') {
            throw new AccountLockedException(
                'User is locked',
                401
            );
        }

        if (!$this->hash->verify($password, $user->password)) {
            $this->app->dispatch(new AuthInvalidPasswordEvent($user));

            throw new InvalidCredentialsException(
                'Invalid credentials',
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

        $this->session->set('user', $data);

        return $this->isLogged();
    }

    /**
     * {@inheritdoc}
     */
    public function logout(): void
    {
        $this->session->remove('user');
    }
}
