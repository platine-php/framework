<?php

declare(strict_types=1);

namespace Platine\Test\Framework\OAuth2\Repository;

use Platine\Framework\OAuth2\Entity\OauthClient;
use Platine\Framework\OAuth2\Repository\ClientRepository;
use Platine\OAuth2\Entity\Client;
use Platine\Orm\EntityManager;
use Platine\Orm\Query\EntityQuery;
use Platine\Orm\Relation\PrimaryKey;

/*
 * @group core
 * @group framework
 */
class ClientRepositoryTest extends BaseTestRepository
{
    public function testConstruct(): void
    {
        $entityManager = $this->getMockInstance(EntityManager::class, [], [
            'getEntityMapper',
        ]);
        $o = new ClientRepository($entityManager);
        $this->assertInstanceOf(ClientRepository::class, $o);
    }

    public function testClientIdExists(): void
    {
        $entity = $this->getMockInstanceMap(OauthClient::class, [
        ]);
        $eq = $this->getMockBuilder(EntityQuery::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $eq->expects($this->exactly(1))
                ->method('find')
                ->will($this->returnValue($entity));

        $entityClass = OauthClient::class;
        $entityManager = $this->getEntityManager([
            'query' => $eq
        ], []);

        $entityManager->expects($this->exactly(1))
                ->method('query')
                ->with($entityClass);


        $o = new ClientRepository($entityManager);

        $res = $o->clientIdExists('client123');
        $this->assertTrue($res);
    }
    public function testFindClientNoResult(): void
    {

        $eq = $this->getMockBuilder(EntityQuery::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $entityClass = OauthClient::class;
        $entityManager = $this->getEntityManager([
            'query' => $eq
        ], []);

        $entityManager->expects($this->exactly(1))
                ->method('query')
                ->with($entityClass);


        $o = new ClientRepository($entityManager);

        $res = $o->findClient('client123');
        $this->assertNull($res);
    }

    public function testFindClientSuccess(): void
    {
        $entity = $this->getMockInstanceMap(OauthClient::class, [
            '__get' => [
                ['scope', ''],
                ['redirect_uri', ''],
                ['name', 'Platine App'],
                ['id', '12345'],
                ['secret', 'secret12345'],
            ]
        ]);
        $eq = $this->getMockBuilder(EntityQuery::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $eq->expects($this->exactly(1))
                ->method('find')
                ->will($this->returnValue($entity));

        $entityClass = OauthClient::class;
        $entityManager = $this->getEntityManager([
            'query' => $eq
        ], []);

        $entityManager->expects($this->exactly(1))
                ->method('query')
                ->with($entityClass);


        $o = new ClientRepository($entityManager);

        $res = $o->findClient('client123');
        $this->assertInstanceOf(Client::class, $res);
        $this->assertEquals('Platine App', $res->getName());
        $this->assertEquals('12345', $res->getId());
    }

    public function testSaveClient(): void
    {

        $client = $this->getMockInstance(Client::class, [
            'getId' => '12345',
            'getName' => 'Platine App',
            'getSecret' => 'secret#123',
            'getRedirectUris' => [],
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

        $o = new ClientRepository($entityManager);

        $res = $o->saveClient($client);
        $this->assertInstanceOf(Client::class, $res);
    }
}
