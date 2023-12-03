<?php

declare(strict_types=1);

namespace Platine\Framework\OAuth2\Entity;

use Platine\Orm\Entity;
use Platine\Orm\Mapper\EntityMapperInterface;

/**
* @class OauthRefreshToken
* @package Platine\Framework\OAuth2\Entity
* @extends Entity<OauthRefreshToken>
*/
class OauthRefreshToken extends Entity
{
    /**
     * @param EntityMapperInterface<OauthRefreshToken> $mapper
     * @return void
     */
    public static function mapEntity(EntityMapperInterface $mapper): void
    {
         $mapper->primaryKey('refresh_token');
         $mapper->useTimestamp();
         $mapper->casts([
            'expires' => 'date',
            'created_at' => 'date',
            'updated_at' => '?date',
         ]);
    }
}
