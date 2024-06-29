<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Auth\Entity;

use Platine\Database\Connection;
use Platine\Database\ResultSet;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Auth\Entity\Permission;
use Platine\Framework\Auth\Repository\PermissionRepository;
use Platine\Orm\EntityManager;
use Platine\Orm\Mapper\EntityMapper;
use Platine\Orm\Relation\PrimaryKey;

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

    public function testFilters(): void
    {
        $middleResultSet = $this->getMockInstance(ResultSet::class, [
            'get' => [
                'id' => 1,
                'code' => 'create_user',
                'description' => 'User Creation',
                'parent' => 2
            ]
        ]);

        $resultSet = $this->getMockInstance(ResultSet::class, [
            'fetchAssoc' => $middleResultSet
        ]);


        $primaryKey = $this->getMockInstance(PrimaryKey::class, [
            'columns' => ['id']
        ]);
        $entityMapper = $this->getMockInstance(EntityMapper::class, [
            'getPrimaryKey' => $primaryKey,
            'getEntityClass' => Permission::class,
        ], [
            'filter',
            'getFilters',
        ]);

        $cnx = $this->getMockInstance(Connection::class, [
            'query' => $resultSet
        ]);
        $entityManager = $this->getMockInstance(EntityManager::class, [
            'getConnection' => $cnx,
            'getEntityMapper' => $entityMapper,
        ], [
            'query'
        ]);

        $repository = new PermissionRepository($entityManager);
        $entity = $repository->create();
        $this->runPrivateProtectedMethod($entity, 'mapEntity', [$entityMapper]);
        $entityInfo = $repository->filters(['parent' => 2])->find(1);
        $this->assertInstanceOf(Permission::class, $entityInfo);
    }
}
