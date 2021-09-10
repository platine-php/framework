<?php

declare(strict_types=1);

namespace Platine\Test\Auth\Entity;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Auth\Entity\Permission;
use Platine\Framework\Auth\Repository\PermissionRepository;
use Platine\Orm\EntityManager;
use Platine\Orm\Mapper\EntityMapper;

/*
 * @group core
 * @group framework
 */
class PermissionTest extends PlatineTestCase
{

    public function testMapEntity(): void
    {
        $entityMapper = $this->getMockInstance(EntityMapper::class);
        $entityMapper->expects($this->exactly(1))
                ->method('useTimestamp');

        $entityMapper->expects($this->exactly(1))
                ->method('casts');

        $entityManager = $this->getMockInstance(EntityManager::class, [], [
            'getEntityMapper',
        ]);
        $repository = new PermissionRepository($entityManager);
        $entity = $repository->create([]);
        $this->assertInstanceOf(Permission::class, $entity);

        $this->runPrivateProtectedMethod($entity, 'mapEntity', [$entityMapper]);
    }
}
