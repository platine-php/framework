<?php

declare(strict_types=1);

namespace Platine\Test\Framework\OAuth2\Repository;

use DateTime;
use Platine\Database\Query\Delete;
use Platine\Database\Query\Where;
use Platine\Framework\OAuth2\Entity\OauthAuthorizationCode;
use Platine\Framework\OAuth2\Repository\AuthorizationCodeRepository;
use Platine\Framework\OAuth2\User\TokenOwner;
use Platine\OAuth2\Entity\AuthorizationCode;
use Platine\OAuth2\Entity\Client;
use Platine\OAuth2\Service\ClientService;
use Platine\Orm\EntityManager;
use Platine\Orm\Query\EntityQuery;
use Platine\Orm\Relation\PrimaryKey;

/*
 * @group core
 * @group framework
 */
class AuthorizationCodeRepositoryTest extends BaseTestRepository
{
    public function testConstruct(): void
    {
        $entityManager = $this->getMockInstance(EntityManager::class, [], [
            'getEntityMapper',
        ]);
        $clientService = $this->getMockInstance(ClientService::class);
        $o = new AuthorizationCodeRepository($entityManager, $clientService);
        $this->assertInstanceOf(AuthorizationCodeRepository::class, $o);
    }

    public function testCleanExpiredTokens(): void
    {
        $delete = $this->getMockBuilder(Delete::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $where = $this->getMockBuilder(Where::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $where->expects($this->exactly(1))
                ->method('lte')
                ->will($this->returnValue($delete));

        $eq = $this->getMockBuilder(EntityQuery::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $eq->expects($this->exactly(1))
                ->method('where')
                ->will($this->returnValue($where));

        $entityClass = OauthAuthorizationCode::class;
        $entityManager = $this->getEntityManager([
            'query' => $eq
        ], []);

        $entityManager->expects($this->exactly(1))
                ->method('query')
                ->with($entityClass);


        $clientService = $this->getMockInstance(ClientService::class);
        $o = new AuthorizationCodeRepository($entityManager, $clientService);
        $o->cleanExpiredTokens();
    }

    public function testDeleteToken(): void
    {
        $delete = $this->getMockBuilder(Delete::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $where = $this->getMockBuilder(Where::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $where->expects($this->exactly(1))
                ->method('is')
                ->will($this->returnValue($delete));

        $eq = $this->getMockBuilder(EntityQuery::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $eq->expects($this->exactly(1))
                ->method('where')
                ->will($this->returnValue($where));

        $entityClass = OauthAuthorizationCode::class;
        $entityManager = $this->getEntityManager([
            'query' => $eq
        ], []);

        $entityManager->expects($this->exactly(1))
                ->method('query')
                ->with($entityClass);


        $clientService = $this->getMockInstance(ClientService::class);
        $o = new AuthorizationCodeRepository($entityManager, $clientService);

        $token = $this->getMockInstance(AuthorizationCode::class);
        $o->deleteToken($token);
    }

    public function testGetByTokenNoResult(): void
    {

        $eq = $this->getMockBuilder(EntityQuery::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $entityClass = OauthAuthorizationCode::class;
        $entityManager = $this->getEntityManager([
            'query' => $eq
        ], []);

        $entityManager->expects($this->exactly(1))
                ->method('query')
                ->with($entityClass);


        $clientService = $this->getMockInstance(ClientService::class);
        $o = new AuthorizationCodeRepository($entityManager, $clientService);

        $res = $o->getByToken('my_token');
        $this->assertNull($res);
    }

    public function testGetByTokenSuccess(): void
    {
        $entity = $this->getMockInstanceMap(OauthAuthorizationCode::class, [
            '__get' => [
                ['scope', ''],
                ['authorization_code', 'my_token'],
                ['client_id', '12345'],
                ['redirect_uri', '12345'],
            ]
        ]);
        $eq = $this->getMockBuilder(EntityQuery::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $eq->expects($this->exactly(1))
                ->method('find')
                ->will($this->returnValue($entity));

        $entityClass = OauthAuthorizationCode::class;
        $entityManager = $this->getEntityManager([
            'query' => $eq
        ], []);

        $entityManager->expects($this->exactly(1))
                ->method('query')
                ->with($entityClass);


        $clientService = $this->getMockInstance(ClientService::class);
        $o = new AuthorizationCodeRepository($entityManager, $clientService);

        $res = $o->getByToken('my_token');
        $this->assertInstanceOf(AuthorizationCode::class, $res);
        $this->assertEquals('my_token', $res->getToken());
    }

    public function testIsTokenExists(): void
    {
        $entity = $this->getMockInstanceMap(OauthAuthorizationCode::class, [
        ]);
        $eq = $this->getMockBuilder(EntityQuery::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $eq->expects($this->exactly(1))
                ->method('find')
                ->will($this->returnValue($entity));

        $entityClass = OauthAuthorizationCode::class;
        $entityManager = $this->getEntityManager([
            'query' => $eq
        ], []);

        $entityManager->expects($this->exactly(1))
                ->method('query')
                ->with($entityClass);


        $clientService = $this->getMockInstance(ClientService::class);
        $o = new AuthorizationCodeRepository($entityManager, $clientService);

        $res = $o->isTokenExists('my_token');
        $this->assertTrue($res);
    }

    public function testSaveAuthorizationCode(): void
    {
        $owner = $this->getMockInstance(TokenOwner::class, [
            'getOwnerId' => 1
        ]);
        $client = $this->getMockInstance(Client::class, [
            'getId' => '12345'
        ]);

        $token = $this->getMockInstance(AuthorizationCode::class, [
            'getClient' => $client,
            'getOwner' => $owner,
            'getToken' => 'my_token',
            'getExpireAt' => new DateTime(),
            'getScopes' => ['read', 'write'],
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

        $clientService = $this->getMockInstance(ClientService::class);
        $o = new AuthorizationCodeRepository($entityManager, $clientService);

        $res = $o->saveCode($token);
        $this->assertInstanceOf(AuthorizationCode::class, $res);
    }
}
