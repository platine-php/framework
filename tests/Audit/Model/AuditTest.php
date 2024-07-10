<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Audit\Model;

use Platine\Database\Connection;
use Platine\Database\ResultSet;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Audit\Model\Audit;
use Platine\Framework\Audit\Model\AuditRepository;
use Platine\Orm\EntityManager;
use Platine\Orm\Mapper\EntityMapper;
use Platine\Orm\Relation\PrimaryKey;

/*
 * @group core
 * @group framework
 */
class AuditTest extends PlatineTestCase
{
    public function testMapEntity(): void
    {
        $entityMapper = $this->getMockInstance(EntityMapper::class);


        $entityMapper->expects($this->exactly(1))
                ->method('casts');

        $entityMapper->expects($this->exactly(1))
                ->method('relation');

        $entityManager = $this->getMockInstance(EntityManager::class, [], [
            'getEntityMapper',
        ]);
        $repository = new AuditRepository($entityManager);
        $entity = $repository->create([]);
        $this->assertInstanceOf(Audit::class, $entity);

        $this->runPrivateProtectedMethod($entity, 'mapEntity', [$entityMapper]);
    }

    public function testFilters(): void
    {
        $middleResultSet = $this->getMockInstance(ResultSet::class, [
            'get' => [
                'id' => 1,
                'event' => 'delete',
                'user_id' => 1,
                'date' => '2022-03-22',
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
            'getEntityClass' => Audit::class,
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

        $repository = new AuditRepository($entityManager);
        $entity = $repository->create();
        $this->runPrivateProtectedMethod($entity, 'mapEntity', [$entityMapper]);
        $entityInfo = $repository->filters([
            'search' => 'delete',
            'event' => 'delete',
            'user' => 1,
            'start_date' => '2022-03-23',
            'end_date' => '2022-03-24',
         ])->find(1);

        $this->assertInstanceOf(Audit::class, $entityInfo);
    }
}
