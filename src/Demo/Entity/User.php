<?php

namespace Platine\Framework\Demo\Entity;

use Platine\Orm\Entity;
use Platine\Orm\Mapper\EntityMapperInterface;

/**
 * Description of User
 *
 * @author tony
 */
class User extends Entity
{

    public static function mapEntity(EntityMapperInterface $mapper): void
    {
        $mapper->primaryKey('user_id');
    }
}
