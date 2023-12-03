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
 *  @file ScopeRepository.php
 *
 *  The Scope Repository class
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

use Platine\Framework\OAuth2\Entity\OauthScope;
use Platine\OAuth2\Entity\Scope;
use Platine\OAuth2\Repository\ScopeRepositoryInterface;
use Platine\Orm\EntityManager;
use Platine\Orm\Repository;

/**
 * @class ScopeRepository
 * @package Platine\Framework\OAuth2\Repository
 * @extends Repository<OauthScope>
 */
class ScopeRepository extends Repository implements ScopeRepositoryInterface
{
    /**
     * Create new instance
     * @param EntityManager<OauthScope> $manager
     */
    public function __construct(EntityManager $manager)
    {
        parent::__construct($manager, OauthScope::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllScopes(): array
    {
        $result = $this->all();
        $scopes = [];
        foreach ($result as $row) {
            $scopes[] = Scope::createNewScope(
                $row->id,
                $row->name,
                $row->description,
                $row->is_default === 1
            );
        }
        return $scopes;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultScopes(): array
    {
        $result = $this->query()
                       ->where('is_default')->is(1)
                       ->all();
        $scopes = [];
        foreach ($result as $row) {
            $scopes[] = Scope::createNewScope(
                $row->id,
                $row->name,
                '',
                $row->is_default === 1
            );
        }
        return $scopes;
    }

    /**
     * {@inheritdoc}
     */
    public function saveScope(Scope $scope): Scope
    {
        $newScope = $this->create([
            'name' => $scope->getName(),
            'description' => $scope->getDescription(),
            'is_default' => $scope->isDefault() ? 1 : 0,
        ]);

        $this->save($newScope);

        return $scope;
    }
}
