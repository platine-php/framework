<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Auth\Entity;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Auth\Entity\Permission;
use Platine\Framework\Auth\Entity\Role;
use Platine\Framework\Auth\Repository\RoleRepository;
use Platine\Orm\EntityManager;
use Platine\Orm\Mapper\EntityMapper;

/*
 * @group core
 * @group framework
 */
class RoleTest extends PlatineTestCase
{
    public function testMapEntity(): void
    {
        $entityMapper = $this->getMockInstance(EntityMapper::class);
        $entityMapper->expects($this->exactly(1))
                ->method('useTimestamp');

        $entityMapper->expects($this->exactly(1))
                ->method('relation');

        $entityMapper->expects($this->exactly(1))
                ->method('casts');

        $entityManager = $this->getMockInstance(EntityManager::class, [], [
            'getEntityMapper',
        ]);
        $repository = new RoleRepository($entityManager);
        $entity = $repository->create([]);
        $this->assertInstanceOf(Role::class, $entity);

        $this->runPrivateProtectedMethod($entity, 'mapEntity', [$entityMapper]);
    }

    public function testSetRemovePermissions(): void
    {
        $permission = $this->getMockInstance(Permission::class);

        $entityManager = $this->getMockInstance(EntityManager::class, [], [
            'getEntityMapper'
        ]);
        $repository = new RoleRepository($entityManager);
        /** @var Role $entity */
        $entity = $repository->create([]);
        $this->assertInstanceOf(Role::class, $entity);
        $this->assertInstanceOf(Role::class, $entity->setPermissions([$permission]));
        $this->assertInstanceOf(Role::class, $entity->removePermissions([$permission]));
    }
}
