<?php

declare(strict_types=1);

namespace Platine\Framework\OAuth2\Entity;

use Platine\Orm\Entity;
use Platine\Orm\Mapper\EntityMapperInterface;

/**
* @class OauthScope
* @package Platine\Framework\OAuth2\Entity
*/
class OauthScope extends Entity
{
    /**
    * {@inheritdoc}
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
