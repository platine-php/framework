<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Auth\Entity;

use Platine\Database\Connection;
use Platine\Database\ResultSet;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Auth\Entity\Role;
use Platine\Framework\Auth\Entity\User;
use Platine\Framework\Auth\Repository\UserRepository;
use Platine\Orm\EntityManager;
use Platine\Orm\Mapper\EntityMapper;
use Platine\Orm\Relation\PrimaryKey;

/*
 * @group core
 * @group framework
 */
class UserTest extends PlatineTestCase
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
        $repository = new UserRepository($entityManager);
        $entity = $repository->create([]);
        $this->assertInstanceOf(User::class, $entity);

        $this->runPrivateProtectedMethod($entity, 'mapEntity', [$entityMapper]);
    }

    public function testSetRemoveRoles(): void
    {
        $role = $this->getMockInstance(Role::class);

        $entityManager = $this->getMockInstance(EntityManager::class, [], [
            'getEntityMapper'
        ]);
        $repository = new UserRepository($entityManager);
        /** @var User $entity */
        $entity = $repository->create([]);
        $this->assertInstanceOf(User::class, $entity);
        $this->assertInstanceOf(User::class, $entity->setRoles([$role]));
        $this->assertInstanceOf(User::class, $entity->removeRoles([$role]));
    }

    public function testGetIdentity(): void
    {
        $middleResultSet = $this->getMockInstance(ResultSet::class, [
            'get' => [
                'id' => 1,
                'firstname' => 'Tony',
                'lastname' => 'NGUEREZA',
                'username' => 'TNH'
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
            'getEntityClass' => User::class,
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

        $repository = new UserRepository($entityManager);
        /** @var User $entity */
        $entity = $repository->find(1);
        $this->assertInstanceOf(User::class, $entity);
        $this->assertEquals(1, $entity->getId());
        $this->assertEquals('Tony NGUEREZA', $entity->getName());
        $this->assertEquals('TNH', $entity->getUsername());
    }

    public function testFilters(): void
    {
        $middleResultSet = $this->getMockInstance(ResultSet::class, [
            'get' => [
                'id' => 1,
                'firstname' => 'Tony',
                'lastname' => 'NGUEREZA',
                'username' => 'TNH'
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
            'getEntityClass' => User::class,
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

        $repository = new UserRepository($entityManager);
        $entity = $repository->create();
        $this->runPrivateProtectedMethod($entity, 'mapEntity', [$entityMapper]);
        $entityInfo = $repository->filters(['status' => 'A'])->find(1);
        $this->assertInstanceOf(User::class, $entityInfo);
    }
}
