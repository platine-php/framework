<?php

declare(strict_types=1);

namespace Platine\Framework\OAuth2\Entity;

use Platine\Orm\Entity;
use Platine\Orm\Mapper\EntityMapperInterface;

/**
* @class OauthClient
* @package Platine\Framework\OAuth2\Entity
* @extends Entity<OauthClient>
*/
class OauthClient extends Entity
{
    /**
     *
     * @param EntityMapperInterface<OauthClient> $mapper
     * @return void
     */
    public static function mapEntity(EntityMapperInterface $mapper): void
    {
         $mapper->useTimestamp();
         $mapper->casts([
            'created_at' => 'date',
            'updated_at' => '?date',
         ]);
    }
}
