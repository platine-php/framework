<?php

declare(strict_types=1);

namespace Platine\Test\Framework\OAuth2\Entity;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\OAuth2\Entity\OauthAccessToken;
use Platine\Framework\OAuth2\Repository\AccessTokenRepository;
use Platine\OAuth2\Service\ClientService;
use Platine\Orm\EntityManager;
use Platine\Orm\Mapper\EntityMapper;

/*
 * @group core
 * @group framework
 */
class OauthAccessTokenTest extends PlatineTestCase
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
                ->method('casts');

        $entityManager = $this->getMockInstance(EntityManager::class, [], [
            'getEntityMapper',
        ]);
        $clientService = $this->getMockInstance(ClientService::class);
        $repository = new AccessTokenRepository($entityManager, $clientService);
        $entity = $repository->create([]);
        $this->assertInstanceOf(OauthAccessToken::class, $entity);

        $this->runPrivateProtectedMethod($entity, 'mapEntity', [$entityMapper]);
    }
}
