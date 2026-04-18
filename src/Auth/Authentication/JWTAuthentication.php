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
use Platine\Container\ContainerInterface;
use Platine\Framework\App\Application;
use Platine\Framework\Auth\Authorization\PermissionCacheInterface;
use Platine\Framework\Auth\Entity\Token;
use Platine\Framework\Auth\Entity\User;
use Platine\Framework\Auth\Enum\UserStatus;
use Platine\Framework\Auth\Event\AuthLoginEvent;
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
use Platine\Stdlib\Helper\Arr;
use Platine\Stdlib\Helper\Str;

/**
 * @class JWTAuthentication
 * @package Platine\Framework\Auth\Authentication
 * @template T
 */
class JWTAuthentication extends BaseAuthentication
{
    /**
     * Create new instance
     * @param HashInterface $hash
     * @param UserRepository $userRepository
     * @param Application $app
     * @param JWT $jwt
     * @param LoggerInterface $logger
     * @param Config<T> $config
     * @param TokenRepository $tokenRepository
     * @param ContainerInterface $container
     * @param PermissionCacheInterface $permissionCache
     */
    public function __construct(
        HashInterface $hash,
        UserRepository $userRepository,
        Application $app,
        protected JWT $jwt,
        protected LoggerInterface $logger,
        protected Config $config,
        protected TokenRepository $tokenRepository,
        protected ContainerInterface $container,
        protected PermissionCacheInterface $permissionCache
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
        if ($this->isLogged() === false) {
            return [];
        }

        $roles = $this->getJwtPayload('roles', []);
        $permissions = [];
        foreach ($roles as $id) {
            // TODO: add configuration for prefix
            $roleKey = sprintf('api_role_%d', $id);
            $pers = $this->permissionCache->get($roleKey, []);
            if (is_array($pers)) {
                $permissions = [
                    ...$pers,
                    ...$permissions
                ];
            }
        }

        if (count($permissions) > 0) {
            return array_unique($permissions);
        }

        // May be cache feature not available or expired
        // get user permissions from database
        $id = (int) $this->getJwtPayload('sub', -1);
        return $this->getUserPermissions($id);
    }


    /**
     * {@inheritdoc}
     */
    public function getId(): int|string
    {
        if ($this->isLogged() === false) {
            throw new AccountNotFoundException('User not logged', 401);
        }

        $id = $this->getAuthAttribute('sub');

        return $id;
    }

    /**
     * {@inheritdoc}
     */
    public function isLogged(): bool
    {
        $request = null;
        if ($this->container->has(ServerRequestInterface::class)) {
            /** @var ServerRequestInterface $request */
            $request = $this->container->get(ServerRequestInterface::class);
        }

        if ($request === null) {
            return false;
        }

        $headerName = $this->config->get('api.auth.headers.name', 'Authorization');
        $tokenHeader = $request->getHeaderLine($headerName);
        if (empty($tokenHeader)) {
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
    public function login(
        array $credentials = [],
        bool $rememberMe = false,
        bool $withPassword = true
    ): array {
        if (isset($credentials['username']) === false) {
            throw new MissingCredentialsException(
                'Missing username information',
                401
            );
        }

        if ($withPassword && isset($credentials['password']) === false) {
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

        // Generate the JWT token
        $this->generateJwtToken($user);

        // Inform the system that the user just login successfully
        $this->app->dispatch(new AuthLoginEvent($user));

        return $this->getLoginData($user);
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

        // Generate the JWT token
        $this->generateJwtToken($user);

        $loginData = $this->getLoginData($user);

        return $loginData;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthAttribute(string $key, mixed $default = null): mixed
    {
        return $this->getJwtPayload($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function logout(bool $destroy = true): void
    {
        if ($this->isLogged() === false) {
            return;
        }
        $userId = $this->getId();
        $this->tokenRepository->query()
                              ->where('user_id')->is($userId)
                              ->delete();
    }

    /**
     * Generate the JWT token
     * @param User $user
     * @return void
     */
    protected function generateJwtToken(User $user): void
    {
        $roles = Arr::getColumn($user->roles, 'id');
        $secret = $this->config->get('api.sign.secret');
        $expire = $this->config->get('api.auth.token_expire', 900);
        $tokenExpire = time() + $expire;
        $payload = [
            'sub' => $user->id,
            'exp' => $tokenExpire,
            'roles' => $roles,
        ] + $this->getUserAttribute($user);
        $this->jwt->setSecret($secret)
                  ->setPayload($payload)
                  ->sign();
    }

    /**
     * {@inheritdoc}
     */
    protected function getLoginData(User $user): array
    {
        $loginData = parent::getLoginData($user);
        $jwtToken = $this->jwt->getToken();
        [$refreshToken, ] = $this->generateRefreshToken($user->id);

        return $loginData  + ['token' => $jwtToken,
          'refresh_token' => $refreshToken,];
    }

    /**
     * Generate the refresh token of the given user
     * @param int $userId
     * @return array{0: string, 1: Token}
     */
    protected function generateRefreshToken(int $userId): array
    {
        $refreshExpire = $this->config->get('api.auth.refresh_token_expire', 30 * 86400);
        $refreshTokenExpire = time() + $refreshExpire;
        $refreshToken = Str::random(128);
        $refreshTokenHash = hash('sha256', $refreshToken);

        /** @var Token|null $token */
        $token = $this->tokenRepository->filters(['user' => $userId])
                                       ->query()
                                       ->get();

        if ($token === null) {
            /** @var Token $token */
            $token = $this->tokenRepository->create([]);
        }
        $token->refresh_token = $refreshTokenHash;
        $token->expire_at = (new DateTime())->setTimestamp($refreshTokenExpire);
        $token->user_id = $userId;

        $this->tokenRepository->save($token);

        return [$refreshToken, $token];
    }

    /**
     * Return the JWT payload value
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function getJwtPayload(string $key, mixed $default = null): mixed
    {
        $payload = $this->jwt->getPayload();
        return $payload[$key] ?? $default;
    }
}
