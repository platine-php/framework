<?php

declare(strict_types=1);

namespace Platine\Test\Framework\OAuth2\Repository;

use Platine\Database\Query\Where;
use Platine\Framework\OAuth2\Entity\OauthScope;
use Platine\Framework\OAuth2\Repository\ScopeRepository;
use Platine\OAuth2\Entity\Scope;
use Platine\Orm\EntityManager;
use Platine\Orm\Query\EntityQuery;
use Platine\Orm\Relation\PrimaryKey;

/*
 * @group core
 * @group framework
 */
class ScopeRepositoryTest extends BaseTestRepository
{
    public function testConstruct(): void
    {
        $entityManager = $this->getMockInstance(EntityManager::class, [], [
            'getEntityMapper',
        ]);
        $o = new ScopeRepository($entityManager);
        $this->assertInstanceOf(ScopeRepository::class, $o);
    }

    public function testGetAllScopes(): void
    {
        $entity = $this->getMockInstanceMap(OauthScope::class, [
            '__get' => [
                ['id', 1],
                ['name', 'read'],
                ['description', 'read everything'],
                ['is_default ', 1],
            ]
        ]);
        $eq = $this->getMockBuilder(EntityQuery::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $eq->expects($this->exactly(1))
                ->method('all')
                ->will($this->returnValue([$entity]));

        $entityClass = OauthScope::class;
        $entityManager = $this->getEntityManager([
            'query' => $eq
        ], []);

        $entityManager->expects($this->exactly(1))
                ->method('query')
                ->with($entityClass);


        $o = new ScopeRepository($entityManager);

        $res = $o->getAllScopes();
        $this->assertCount(1, $res);
    }

    public function testGetDefaultScopes(): void
    {
        $entity = $this->getMockInstanceMap(OauthScope::class, [
            '__get' => [
                ['id', 1],
                ['name', 'read'],
                ['description', 'read everything'],
                ['is_default ', 1],
            ]
        ]);

        $eq = $this->getMockBuilder(EntityQuery::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $where = $this->getMockBuilder(Where::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $where->expects($this->exactly(1))
                ->method('is')
                ->will($this->returnValue($eq));

        $eq->expects($this->exactly(1))
                ->method('where')
                ->will($this->returnValue($where));

        $eq->expects($this->exactly(1))
                ->method('all')
                ->will($this->returnValue([$entity]));

        $entityClass = OauthScope::class;
        $entityManager = $this->getEntityManager([
            'query' => $eq
        ], []);

        $entityManager->expects($this->exactly(1))
                ->method('query')
                ->with($entityClass);


        $o = new ScopeRepository($entityManager);

        $res = $o->getDefaultScopes();
        $this->assertCount(1, $res);
    }


    public function testSaveScope(): void
    {

        $scope = $this->getMockInstance(Scope::class, [
            'getName' => 'read',
            'getDescription' => 'read all',
            'isDefault' => true,
        ]);

        $eq = $this->getMockBuilder(EntityQuery::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $primaryKey = $this->getMockBuilder(PrimaryKey::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $primaryKey->expects($this->any())
                ->method('columns')
                ->will($this->returnValue(['id']));

        $primaryKey->expects($this->any())
                ->method('getValue')
                ->will($this->returnValue(['id' => 1]));

        $entityMapper = $this->getEntityMapper([
            'getPrimaryKey' => $primaryKey,
            'getTable' => 'my_table',
        ], []);

        $entityManager = $this->getEntityManager([
            'query' => $eq,
            'getEntityMapper' => $entityMapper
        ], []);

        $o = new ScopeRepository($entityManager);

        $res = $o->saveScope($scope);
        $this->assertInstanceOf(Scope::class, $res);
    }
}
