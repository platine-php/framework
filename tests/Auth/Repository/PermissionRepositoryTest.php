<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Auth\Repository;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Auth\Entity\Permission;
use Platine\Framework\Auth\Repository\PermissionRepository;
use Platine\Orm\EntityManager;

/*
 * @group core
 * @group framework
 */
class PermissionRepositoryTest extends PlatineTestCase
{
    public function testGetTree(): void
    {
        $manager = $this->getMockInstance(EntityManager::class);

        $o = new PermissionRepository($manager);
        $tree = $o->getTree();

        $this->assertCount(0, $tree);
    }

    public function testGetPermissionTree(): void
    {
        $one = $this->getMockInstanceMap(Permission::class, [
            '__get' => [
                ['id', 1],
                ['code', 'login'],
                ['description', 'can login'],
                ['parent_id', null],
            ],
        ]);
        $permissions = [$one];

        $tree = PermissionRepository::getPermissionTree($permissions);

        $this->assertCount(1, $tree);
    }
}
