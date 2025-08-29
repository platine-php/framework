<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Orm;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Orm\StandardRepository;
use Platine\Orm\EntityManager;
use Platine\Orm\Mapper\EntityMapper;

class StandardRepositoryTest extends PlatineTestCase
{
    public function testMapEntity(): void
    {
        $manager = $this->getMockInstance(EntityManager::class, [], ['getEntityMapper']);
        $o = new StandardRepository($manager);

        $this->assertInstanceOf(StandardRepository::class, $o);
        $this->assertInstanceOf(EntityMapper::class, $o->getEntityMapper());

        $o->setTable('users');
        $o->addFilter('status', function ($val) {
        });
        $this->assertEquals($o->getEntityMapper()->getTable(), 'users');
        $this->assertArrayHasKey('status', $o->getEntityMapper()->getFilters());
    }
}
