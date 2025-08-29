<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Orm;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Orm\StandardEntity;
use Platine\Orm\EntityManager;
use Platine\Orm\Mapper\EntityMapper;

class StandardEntityTest extends PlatineTestCase
{
    public function testMapEntity(): void
    {
        $manager = $this->getMockInstance(EntityManager::class);
        $mapper = $this->getMockInstance(EntityMapper::class);
        $o = new StandardEntity($manager, $mapper);

        $this->assertInstanceOf(StandardEntity::class, $o);
        $o->mapEntity($mapper);

        $this->expectMethodCallCount($mapper, 'getCasts', 2);
        $this->expectMethodCallCount($mapper, 'getPrimaryKey');
        $this->expectMethodCallCount($mapper, 'getSetters');
        $o->name = 'foo';
        $name = $o->name;
        $this->assertEquals($name, 'foo');
    }
}
