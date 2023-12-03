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
 *  @file ClientRepository.php
 *
 *  The Client Repository class
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

use Platine\Framework\OAuth2\Entity\OauthClient;
use Platine\OAuth2\Entity\Client;
use Platine\OAuth2\Repository\ClientRepositoryInterface;
use Platine\Orm\EntityManager;
use Platine\Orm\Repository;

/**
 * @class ClientRepository
 * @package Platine\Framework\OAuth2\Repository
 * @extends Repository<OauthClient>
 */
class ClientRepository extends Repository implements ClientRepositoryInterface
{
    /**
     * Create new instance
     * @param EntityManager<OauthClient> $manager
     */
    public function __construct(EntityManager $manager)
    {
        parent::__construct($manager, OauthClient::class);
    }

    /**
     * {@inheritdoc}
     */
    public function clientIdExists(string $id): bool
    {
        return $this->find($id) !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function findClient(string $id): ?Client
    {
        $client = $this->find($id);
        if ($client === null) {
            return null;
        }
        return Client::hydrate([
            'name' => $client->name,
            'id' => $client->id,
            'redirect_uris' => explode(' ', $client->redirect_uri),
            'secret' => $client->secret,
            'scopes' => explode(' ', $client->scope),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function saveClient(Client $client): Client
    {
        $newClient = $this->create([
            'id' => $client->getId(),
            'name' => $client->getName(),
            'secret' => $client->getSecret(),
            'grant_types' => null, // TODO
            'redirect_uri' => implode(' ', $client->getRedirectUris()),
            'scope' => implode(' ', $client->getScopes()),
            'user_id' => null, // TODO
        ]);

        $this->save($newClient);

        return $client;
    }
}
