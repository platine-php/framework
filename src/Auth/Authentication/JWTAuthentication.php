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
 *  @file JWTAuthentication.php
 *
 *  The Authentication using JWT class
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

use DateTime;
use Platine\Config\Config;
use Platine\Framework\Auth\ApiAuthenticationInterface;
use Platine\Framework\Auth\Entity\Token;
use Platine\Framework\Auth\Entity\User;
use Platine\Framework\Auth\Enum\UserStatus;
use Platine\Framework\Auth\Exception\AccountLockedException;
use Platine\Framework\Auth\Exception\AccountNotFoundException;
use Platine\Framework\Auth\Exception\InvalidCredentialsException;
use Platine\Framework\Auth\Exception\MissingCredentialsException;
use Platine\Framework\Auth\IdentityInterface;
use Platine\Framework\Auth\Repository\TokenRepository;
use Platine\Framework\Auth\Repository\UserRepository;
use Platine\Framework\Security\JWT\Exception\JWTException;
use Platine\Framework\Security\JWT\JWT;
use Platine\Http\ServerRequestInterface;
use Platine\Logger\LoggerInterface;
use Platine\Security\Hash\HashInterface;
use Platine\Stdlib\Helper\Str;

/**
 * @class JWTAuthentication
 * @package Platine\Framework\Auth\Authentication
 * @template T
 */
class JWTAuthentication implements ApiAuthenticationInterface
{
    /**
     * The JWT instance
     * @var JWT
     */
    protected JWT $jwt;

    /**
     * The logger instance
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * The configuration instance
     * @var Config<T>
     */
    protected Config $config;

    /**
     * The user repository instance
     * @var UserRepository
     */
    protected UserRepository $userRepository;

    /**
     * The token repository
     * @var TokenRepository
     */
    protected TokenRepository $tokenRepository;

    /**
     * Hash instance to use
     * @var HashInterface
     */
    protected HashInterface $hash;

    /**
     * The server request instance
     * @var ServerRequestInterface
     */
    protected ServerRequestInterface $request;

    /**
     * Create new instance
     * @param JWT $jwt
     * @param LoggerInterface $logger
     * @param Config<T> $config
     * @param HashInterface $hash
     * @param UserRepository $userRepository
     * @param TokenRepository $tokenRepository
     * @param ServerRequestInterface $request
     */
    public function __construct(
        JWT $jwt,
        LoggerInterface $logger,
        Config $config,
        HashInterface $hash,
        UserRepository $userRepository,
        TokenRepository $tokenRepository,
        ServerRequestInterface $request
    ) {
        $this->jwt = $jwt;
        $this->logger = $logger;
        $this->config = $config;
        $this->hash = $hash;
        $this->userRepository = $userRepository;
        $this->tokenRepository = $tokenRepository;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser(): IdentityInterface
    {
        if ($this->isAuthenticated($this->request) === false) {
            throw new AccountNotFoundException('User not logged', 401);
        }

        $payload = $this->jwt->getPayload();
        $id = (int) ($payload['sub'] ?? -1);

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
    public function isAuthenticated(ServerRequestInterface $request): bool
    {
        $headerName = $this->config->get('api.auth.headers.name', 'Authorization');
        $tokenHeader = $request->getHeaderLine($headerName);
        if (empty($tokenHeader)) {
            $this->logger->error('API authentication failed missing token header');

            return false;
        }
        $tokenType = $this->config->get('api.auth.headers.token_type', 'Bearer');
        $secret = $this->config->get('api.sign.secret', '');

        $token = Str::replaceFirst($tokenType . ' ', '', $tokenHeader);

        $this->jwt->setSecret($secret);
        try {
            $this->jwt->decode($token);

            return true;
        } catch (JWTException $ex) {
            $this->logger->error('API authentication failed: {message}', [
                'message' => $ex->getMessage(),
            ]);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function login(array $credentials = [], bool $withPassword = true): array
    {
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
                if (!in_array($permission->code, $permissions)) {
                    $permissions[] = $permission->code;
                }
            }
        }

        $secret = $this->config->get('api.sign.secret');
        $expire = $this->config->get('api.auth.token_expire', 900);
        $refreshExpire = $this->config->get('api.auth.refresh_token_expire', 30 * 86400);
        $tokenExpire = time() + $expire;
        $refreshTokenExpire = time() + $refreshExpire;
        $this->jwt->setSecret($secret)
                  ->setPayload([
                      'sub' => $user->id,
                      'exp' => $tokenExpire,
                      'permissions' => $permissions,
                  ])
                  ->sign();

        $refreshToken = Str::random(64);
        $jwtToken = $this->jwt->getToken();

        $token = $this->tokenRepository->create([
            'token' => $jwtToken,
            'refresh_token' => $refreshToken,
            'expire_at' => (new DateTime())->setTimestamp($refreshTokenExpire),
            'user_id' => $user->id,
        ]);

        $this->tokenRepository->save($token);

        $data = [
          'user' => [
            'id' => $user->id,
            'username' => $user->username,
            'lastname' => $user->lastname,
            'firstname' => $user->firstname,
            'email' => $user->email,
            'permissions' => $permissions,
          ],
          'token' => $jwtToken,
          'refresh_token' => $refreshToken,
        ];

        return array_merge($data, $this->getUserData($user, $token));
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
        return $this->userRepository
                                    ->with('roles.permissions')
                                    ->findBy(['username' => $username]);
    }

    /**
     * Return the user additional data
     * @param User $user
     * @param Token $token
     * @return array<string, mixed>
     */
    protected function getUserData(User $user, Token $token): array
    {
        return [
            'refresh_token_expire' => $token->expire_at->getTimestamp()
        ];
    }
}
