<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Auth\Entity;

use Platine\Database\Connection;
use Platine\Database\ResultSet;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Auth\Entity\Token;
use Platine\Framework\Auth\Repository\TokenRepository;
use Platine\Orm\EntityManager;
use Platine\Orm\Mapper\EntityMapper;
use Platine\Orm\Relation\PrimaryKey;

/*
 * @group core
 * @group framework
 */
class TokenTest extends PlatineTestCase
{
    public function testMapEntity(): void
    {
        $entityMapper = $this->getMockInstance(EntityMapper::class, [], [
            'filter',
            'getFilters'
        ]);
        $entityMapper->expects($this->exactly(1))
                ->method('useTimestamp');

        $entityMapper->expects($this->exactly(1))
                ->method('relation');

        $entityMapper->expects($this->exactly(1))
                ->method('casts');

        $entityManager = $this->getMockInstance(EntityManager::class, [], [
            'getEntityMapper',
        ]);
        $repository = new TokenRepository($entityManager);
        $entity = $repository->create([]);
        $this->assertInstanceOf(Token::class, $entity);

        $this->runPrivateProtectedMethod($entity, 'mapEntity', [$entityMapper]);
    }

    public function testFilters(): void
    {
        $middleResultSet = $this->getMockInstance(ResultSet::class, [
            'get' => []
        ]);

        $resultSet = $this->getMockInstance(ResultSet::class, [
            'fetchAssoc' => $middleResultSet
        ]);


        $primaryKey = $this->getMockInstance(PrimaryKey::class, [
            'columns' => ['id']
        ]);
        $entityMapper = $this->getMockInstance(EntityMapper::class, [
            'getPrimaryKey' => $primaryKey,
            'getEntityClass' => Token::class,
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

        $repository = new TokenRepository($entityManager);
        $entity = $repository->create();
        $this->runPrivateProtectedMethod($entity, 'mapEntity', [$entityMapper]);
        $entityInfo = $repository->filters(['not_expire' => '2021-01-02', 'user' => 1])->find(1);
        $this->assertInstanceOf(Token::class, $entityInfo);
    }
}
