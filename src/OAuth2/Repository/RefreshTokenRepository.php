<?php

/**
 * Platine PHP
 *
 * Platine PHP is a lightweight, high-performance, simple and elegant
 * PHP Web framework
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2020 Platine PHP
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
 *  @file RefreshTokenRepository.php
 *
 *  The refresh token Repository class
 *
 *  @package    Platine\Framework\OAuth2\Repository
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\OAuth2\Repository;

use Platine\Framework\OAuth2\Entity\OauthRefreshToken;
use Platine\Framework\OAuth2\User\TokenOwner;
use Platine\OAuth2\Entity\BaseToken;
use Platine\OAuth2\Entity\RefreshToken;
use Platine\OAuth2\Repository\RefreshTokenRepositoryInterface;
use Platine\OAuth2\Service\ClientService;
use Platine\Orm\EntityManager;
use Platine\Orm\Repository;

/**
 * @class RefreshTokenRepository
 * @package Platine\Framework\OAuth2\Repository
 */
class RefreshTokenRepository extends Repository implements RefreshTokenRepositoryInterface
{
    /**
     * The Client Service
     * @var ClientService
     */
    protected ClientService $clientService;

    /**
     * Create new instance
     * @param EntityManager $manager
     * @param ClientService $clientService
     */
    public function __construct(EntityManager $manager, ClientService $clientService)
    {
        parent::__construct($manager, OauthRefreshToken::class);
        $this->clientService = $clientService;
    }

     /**
     * {@inheritdoc}
     */
    public function cleanExpiredTokens(): void
    {
        $this->query()->where('expires')->lte(date('Y-m-d H:i:s'))
                      ->delete();
    }

     /**
     * {@inheritdoc}
     */
    public function deleteToken(BaseToken $token): bool
    {
        return $this->query()->where('refresh_token')->is($token->getToken())
                             ->delete() >= 0;
    }

     /**
     * {@inheritdoc}
     */
    public function getByToken(string $token): ?BaseToken
    {
        $refreshToken = $this->find($token);
        if ($refreshToken === null) {
            return null;
        }

        $client = null;
        if ($refreshToken->client_id !== null) {
            $client = $this->clientService->find($refreshToken->client_id);
        }

        return RefreshToken::hydrate([
            'token' => $refreshToken->refresh_token,
            'owner' => new TokenOwner($refreshToken->user_id),
            'client' => $client,
            'expires_at' => $refreshToken->expires,
            'scopes' => explode(' ', $refreshToken->scope),
        ]);
    }

     /**
     * {@inheritdoc}
     */
    public function isTokenExists(string $token): bool
    {
        return $this->find($token) !== null;
    }

     /**
     * {@inheritdoc}
     */
    public function saveRefreshToken(RefreshToken $token): RefreshToken
    {
        $clientId = null;
        if ($token->getClient() !== null) {
            $clientId = $token->getClient()->getId();
        }

        $ownerId = null;
        if ($token->getOwner() !== null) {
            $ownerId = $token->getOwner()->getOwnerId();
        }

        $refreshToken = $this->create([
            'refresh_token' => $token->getToken(),
            'user_id' => $ownerId,
            'client_id' => $clientId,
            'expires' => $token->getExpireAt(),
            'scope' => implode(' ', $token->getScopes()),
        ]);

        $this->save($refreshToken);

        return $token;
    }
}
