<?php

declare(strict_types=1);

namespace Platine\Framework\OAuth2\Entity;

use Platine\Orm\Entity;
use Platine\Orm\Mapper\EntityMapperInterface;

/**
* @class OauthAccessToken
* @package Platine\Framework\OAuth2\Entity
*/
class OauthAccessToken extends Entity
{
    /**
    * {@inheritdoc}
    */
    public static function mapEntity(EntityMapperInterface $mapper): void
    {
         $mapper->primaryKey('access_token');
         $mapper->useTimestamp();
         $mapper->casts([
            'expires' => 'date',
            'created_at' => 'date',
            'updated_at' => '?date',
         ]);
    }
}