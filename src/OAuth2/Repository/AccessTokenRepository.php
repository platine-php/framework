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
 *  @file AccessTokenRepository.php
 *
 *  The Access Token Repository class
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

use Platine\Framework\OAuth2\Entity\OauthAccessToken;
use Platine\Framework\OAuth2\User\TokenOwner;
use Platine\OAuth2\Entity\AccessToken;
use Platine\OAuth2\Entity\BaseToken;
use Platine\OAuth2\Repository\AccessTokenRepositoryInterface;
use Platine\OAuth2\Service\ClientService;
use Platine\Orm\EntityManager;
use Platine\Orm\Repository;

/**
 * @class AccessTokenRepository
 * @package Platine\Framework\OAuth2\Repository
 * @extends Repository<OauthAccessToken>
 */
class AccessTokenRepository extends Repository implements AccessTokenRepositoryInterface
{
    /**
     * The Client Service
     * @var ClientService
     */
    protected ClientService $clientService;

    /**
     * Create new instance
     * @param EntityManager<OauthAccessToken> $manager
     * @param ClientService $clientService
     */
    public function __construct(EntityManager $manager, ClientService $clientService)
    {
        parent::__construct($manager, OauthAccessToken::class);
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
        return $this->query()->where('access_token')->is($token->getToken())
                             ->delete() >= 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getByToken(string $token): ?BaseToken
    {
        $accessToken = $this->find($token);
        if ($accessToken === null) {
            return null;
        }

        $client = null;
        if ($accessToken->client_id !== null) {
            $client = $this->clientService->find($accessToken->client_id);
        }

        return AccessToken::hydrate([
            'token' => $accessToken->access_token,
            'owner' => new TokenOwner($accessToken->user_id),
            'client' => $client,
            'expires_at' => $accessToken->expires,
            'scopes' => explode(' ', $accessToken->scope),
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
    public function saveAccessToken(AccessToken $token): AccessToken
    {
        $clientId = null;
        if ($token->getClient() !== null) {
            $clientId = $token->getClient()->getId();
        }

        $ownerId = null;
        if ($token->getOwner() !== null) {
            $ownerId = $token->getOwner()->getOwnerId();
        }

        $accessToken = $this->create([
            'access_token' => $token->getToken(),
            'client_id' => $clientId,
            'user_id' => $ownerId,
            'expires' => $token->getExpireAt(),
            'scope' => implode(' ', $token->getScopes()),
        ]);

        $this->save($accessToken);

        return $token;
    }
}
