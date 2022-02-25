<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Config\Model;

use Platine\Database\Connection;
use Platine\Database\ResultSet;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Config\Model\Configuration;
use Platine\Framework\Config\Model\ConfigurationRepository;
use Platine\Orm\EntityManager;
use Platine\Orm\Mapper\EntityMapper;
use Platine\Orm\Relation\PrimaryKey;

/*
 * @group core
 * @group framework
 */
class ConfigurationTest extends PlatineTestCase
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
        $repository = new ConfigurationRepository($entityManager);
        $entity = $repository->create([]);
        $this->assertInstanceOf(Configuration::class, $entity);

        $this->runPrivateProtectedMethod($entity, 'mapEntity', [$entityMapper]);
    }

    public function testFilters(): void
    {
        $middleResultSet = $this->getMockInstance(ResultSet::class, [
            'get' => [
                'id' => 1,
                'module' => 'app',
                'name' => 'tva',
                'value' => '19.6',
                'type' => 'double',
                'status' => 'Y',
                'env' => 'dev',
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
            'getEntityClass' => Configuration::class,
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

        $repository = new ConfigurationRepository($entityManager);
        $entity = $repository->create();
        $this->runPrivateProtectedMethod($entity, 'mapEntity', [$entityMapper]);
        $entityInfo = $repository->filters([
            'status' => 'Y',
            'env' => 'dev',
            'module' => 'app',
            'type' => 'double',
         ])->find(1);

        $this->assertInstanceOf(Configuration::class, $entityInfo);
    }
}
