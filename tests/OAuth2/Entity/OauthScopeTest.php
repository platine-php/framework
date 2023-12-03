<?php

declare(strict_types=1);

namespace Platine\Test\Framework\OAuth2\Entity;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\OAuth2\Entity\OauthScope;
use Platine\Framework\OAuth2\Repository\ScopeRepository;
use Platine\Orm\EntityManager;
use Platine\Orm\Mapper\EntityMapper;

/*
 * @group core
 * @group framework
 */
class OauthScopeTest extends PlatineTestCase
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
        $repository = new ScopeRepository($entityManager);
        $entity = $repository->create([]);
        $this->assertInstanceOf(OauthScope::class, $entity);

        $this->runPrivateProtectedMethod($entity, 'mapEntity', [$entityMapper]);
    }
}
