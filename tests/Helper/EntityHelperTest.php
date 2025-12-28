<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Helper;

use Platine\Database\Connection;
use Platine\Database\ResultSet;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Audit\Auditor;
use Platine\Framework\Auth\Authentication\SessionAuthentication;
use Platine\Framework\Helper\EntityHelper;
use Platine\Orm\Entity;
use Platine\Orm\EntityManager;
use Platine\Orm\Mapper\EntityMapper;
use Platine\Test\Framework\Fixture\MyEntity;
use Platine\Test\Framework\Fixture\MyRepository;

class EntityHelperTest extends PlatineTestCase
{
    public function testConstructor(): void
    {

        $auditor = $this->getMockInstance(Auditor::class);
        $authentication = $this->getMockInstance(SessionAuthentication::class);
        $o = new EntityHelper($auditor, $authentication);

        $this->assertInstanceOf(EntityHelper::class, $o);
        $this->assertFalse($o->isIgnore());
        $o->setIgnore(true);
        $this->assertTrue($o->isIgnore());
    }

    public function testGetEntityChanges(): void
    {
        $auditor = $this->getMockInstance(Auditor::class);
        $authentication = $this->getMockInstance(SessionAuthentication::class);
        $o = new EntityHelper($auditor, $authentication);

        $eRelation1 = $this->getMockInstanceMap(Entity::class, [
            '__get' => [
                ['description', 'foo'],
            ]
        ]);

        $eRelation2 = $this->getMockInstanceMap(Entity::class, [
            '__get' => [
                ['description', 'baz'],
            ]
        ]);

        $e1 = $this->getMockInstanceMap(Entity::class, [
            '__get' => [
                ['name', 'foo'],
                ['status', 'Y'],
                ['relation_field', $eRelation1],
            ]
        ]);

        $e2 = $this->getMockInstanceMap(Entity::class, [
            '__get' => [
                ['name', 'bar'],
                ['status', 'N'],
                ['relation_field', $eRelation2],
            ]
        ]);

        // Both entities are null
        $this->assertCount(0, $o->getEntityChanges(null, null, []));

        $changes = $o->getEntityChanges(
            $e1,
            $e2,
            [
                'name' => [],
                'relation_field' => ['relation' => ['description']],
                'status' => ['enum' => ['N' => 'No', 'Y' => 'Yes']],
            ]
        );
        $this->assertCount(3, $changes);
        $this->assertEquals('name', $changes[0]['name']);
        $this->assertEquals('foo', $changes[0]['old']);
        $this->assertEquals('bar', $changes[0]['new']);

        $this->assertEquals('relation_field', $changes[1]['name']);
        $this->assertEquals('foo', $changes[1]['old']);
        $this->assertEquals('baz', $changes[1]['new']);

        $this->assertEquals('status', $changes[2]['name']);
        $this->assertEquals('Yes', $changes[2]['old']);
        $this->assertEquals('No', $changes[2]['new']);
    }

    public function testGetAttributeChanges(): void
    {
        $auditor = $this->getMockInstance(Auditor::class);
        $authentication = $this->getMockInstance(SessionAuthentication::class);
        $o = new EntityHelper($auditor, $authentication);



        $a1 = [
            1 => [
                'name' => 'firstname',
                'value' => 'Foo',
            ],
        ];
        $a2 = [
            1 => [
                'name' => 'firstname',
                'value' => 'Bar',
            ],
            2 => [
                'name' => 'lastname',
                'value' => 'Bar',
            ],
        ];

        $changes = $o->getAttributeChanges($a1, $a2);

        $this->assertCount(2, $changes);
        $this->assertEquals('firstname', $changes[0]['name']);
        $this->assertEquals('Foo', $changes[0]['old']);
        $this->assertEquals('Bar', $changes[0]['new']);

        $this->assertEquals('lastname', $changes[1]['name']);
        $this->assertEquals(null, $changes[1]['old']);
        $this->assertEquals('Bar', $changes[1]['new']);
    }

    public function testSubscribeEventsNotLogged(): void
    {
        $auditor = $this->getMockInstance(Auditor::class);
        $authentication = $this->getMockInstance(SessionAuthentication::class, [
            'isLogged' => false,
        ]);
        $mapper = $this->getMockInstance(EntityMapper::class);
        $o = new EntityHelper($auditor, $authentication);

        $this->expectMethodCallCount($authentication, 'isLogged');
        $o->subscribeEvents($mapper);
    }

    public function testSubscribeEventsSuccess(): void
    {
        $auditor = $this->getMockInstance(Auditor::class);
        $authentication = $this->getMockInstance(SessionAuthentication::class, [
            'isLogged' => true,
        ]);
        $mapper = $this->getMockInstance(EntityMapper::class);
        $o = new EntityHelper($auditor, $authentication);

        $this->expectMethodCallCount($authentication, 'isLogged');
        $this->expectMethodCallCount($mapper, 'on', 3);
        $o->subscribeEvents($mapper);
    }

    public function testSubscribeEventsCreateIgnore(): void
    {
        $this->subscribeEventsRecordChange(true);
    }

    public function testSubscribeEventsCreateNotIgnore(): void
    {
        $this->subscribeEventsRecordChange(false);
    }

    public function testSubscribeEventsUpdateIgnore(): void
    {
        $this->subscribeEventsRecordChange(true, false);
    }

    public function testSubscribeEventsUpdateNotIgnore(): void
    {
        $this->subscribeEventsRecordChange(false, false);
    }

    public function testSubscribeEventsDeleteIgnore(): void
    {
        $this->subscribeEventsRecordChange(true, false, true);
    }

    public function testSubscribeEventsDeleteNotIgnore(): void
    {
        $this->subscribeEventsRecordChange(false, false, true);
    }

    private function subscribeEventsRecordChange(bool $ignore, bool $create = true, bool $delete = false): void
    {
        $resultSetFinal = $this->getMockInstance(ResultSet::class, [
            'get' => ['name' => 'foo', 'id' => 1],
        ]);

        $resultSet = $this->getMockInstance(ResultSet::class, [
            'fetchAssoc' => $resultSetFinal,
        ]);
        $entityClass = MyEntity::class;
        $mapper = new EntityMapper($entityClass);
        $cnx = $this->getMockInstance(Connection::class, [
            'query' => $resultSet,
        ]);

        $manager = new EntityManager($cnx);
        $repo = new MyRepository($manager, $entityClass);
        $entity = new MyEntity(
            $manager,
            $mapper,
            ['name' => 'foo', 'id' => 1],
            [],
            false,
            $create === true
        );

        if ($create === false) {
            $entity->foo = 'bar';
        }


        $auditor = $this->getMockInstance(Auditor::class);
        $authentication = $this->getMockInstance(SessionAuthentication::class, [
            'isLogged' => true,
        ]);

        $o = new EntityHelper($auditor, $authentication);
        $o->setIgnore($ignore);

        $this->expectMethodCallCount($auditor, 'setDetail', $ignore ? 0 : 1);

        $o->subscribeEvents($mapper);

        if ($delete) {
             $repo->delete($entity);
        } else {
            $repo->save($entity);
        }
    }
}
