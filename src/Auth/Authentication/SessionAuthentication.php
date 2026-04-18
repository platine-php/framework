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
class SessionAuthentication extends BaseAuthentication
{
    /**
     * Create new instance
     * @param HashInterface $hash
     * @param UserRepository $userRepository
     * @param Application $app
     * @param Session $session
     */
    public function __construct(
        HashInterface $hash,
        UserRepository $userRepository,
        Application $app,
        protected Session $session,
    ) {
        parent::__construct($hash, $userRepository, $app);
    }

    /**
     * {@inheritdoc}
     */
    public function getUser(): IdentityInterface
    {
        $id = $this->getId();
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
    public function getPermissions(): array
    {
        return $this->getAuthAttribute('permissions', []);
    }


    /**
     * {@inheritdoc}
     */
    public function getId(): int|string
    {
        if ($this->isLogged() === false) {
            throw new AccountNotFoundException('User not logged', 401);
        }

        return $this->session->get('auth.user.id');
    }

    /**
     * {@inheritdoc}
     */
    public function isLogged(): bool
    {
        return $this->session->has('auth');
    }

    /**
     * {@inheritdoc}
     */
    public function login(
        array $credentials = [],
        bool $rememberMe = false,
        bool $withPassword = true
    ): array {
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

        $loginData = $this->getLoginData($user);

        $this->session->set('auth', $loginData);

        // Inform the system that the user just login successfully
        $this->app->dispatch(new AuthLoginEvent($user));

        return $loginData;
    }

    /**
     * {@inheritdoc}
     */
    public function relogin(IdentityInterface $identity): array
    {
        $id = $identity->getId();
        $user = $this->userRepository->find($id);

        if ($user === null) {
            throw new AccountNotFoundException(
                'Can not find the logged user information, may be data is corrupted',
                401
            );
        }

        $loginData = $this->getLoginData($user);

        $this->session->set('auth', $loginData);

        return $loginData;
    }

    /**
     * {@inheritdoc}
     */
    public function logout(bool $destroy = true): void
    {
        $this->session->remove('auth');

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
     * {@inheritdoc}
     */
    public function getAuthAttribute(string $key, mixed $default = null): mixed
    {
        return $this->session->get(sprintf('auth.%s', $key), $default);
    }
}
