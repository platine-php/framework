<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Migration;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Migration\MigrationEntity;
use Platine\Framework\Migration\MigrationRepository;
use Platine\Orm\EntityManager;
use Platine\Orm\Mapper\EntityMapper;

/*
 * @group core
 * @group framework
 */
class MigrationEntityTest extends PlatineTestCase
{
    public function testMapEntity(): void
    {
        global $mock_app_to_config_instance;
        $mock_app_to_config_instance = true;

        $entityMapper = $this->getMockInstance(EntityMapper::class);
        $entityMapper->expects($this->exactly(1))
                ->method('primaryKey');

        $entityMapper->expects($this->exactly(1))
                ->method('table');

        $entityManager = $this->getMockInstance(EntityManager::class, [], [
            'getEntityMapper',
        ]);
        $repository = new MigrationRepository($entityManager);
        $entity = $repository->create([]);
        $this->assertInstanceOf(MigrationEntity::class, $entity);

        $this->runPrivateProtectedMethod($entity, 'mapEntity', [$entityMapper]);
    }
}
