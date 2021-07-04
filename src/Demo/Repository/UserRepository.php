<?php

namespace Platine\Framework\Demo\Repository;

use Platine\Framework\Demo\Entity\User;
use Platine\Orm\EntityManager;
use Platine\Orm\Repository;

/**
 * Description of UserRepository
 *
 * @author tony
 */
class UserRepository extends Repository
{

    /**
     * Create new instance
     * @param EntityManager $manager
     */
    public function __construct(EntityManager $manager)
    {
        parent::__construct($manager, User::class);
    }
}
