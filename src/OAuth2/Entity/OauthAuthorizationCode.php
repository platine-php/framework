<?php

declare(strict_types=1);

namespace Platine\Framework\OAuth2\Entity;

use Platine\Orm\Entity;
use Platine\Orm\Mapper\EntityMapperInterface;

/**
* @class OauthAuthorizationCode
* @package Platine\Framework\OAuth2\Entity
*/
class OauthAuthorizationCode extends Entity
{
    /**
    * {@inheritdoc}
    */
    public static function mapEntity(EntityMapperInterface $mapper): void
    {
         $mapper->primaryKey('authorization_code');
         $mapper->useTimestamp();
         $mapper->casts([
            'expires' => 'date',
            'created_at' => 'date',
            'updated_at' => '?date',
         ]);
    }
}
