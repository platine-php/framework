<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Form\Param;

use Platine\Dev\PlatineTestCase;
use Platine\Orm\Entity;
use Platine\Test\Framework\Fixture\MyParam;
use Platine\Test\Framework\Fixture\MyParam2;
use Platine\Test\Framework\Fixture\MyParam3;

/*
 * @group core
 * @group framework
 */
class BaseParamTest extends PlatineTestCase
{
    public function testConstructor(): void
    {
        $o = new MyParam([]);
        $this->assertEmpty($o->getDefault());
    }

    public function testLoad(): void
    {
        $o = new MyParam([
            'name' => 'foo',
            'status' => '1'
        ]);
        $this->assertEmpty($o->getDefault());
        $this->assertEquals('foo', $o->getName());
        $this->assertEquals('1', $o->getStatus());

        //Using magic method
        $this->assertEquals('foo', $o->name);
        $this->assertEquals('1', $o->status);
        $this->assertNull($o->undefined);
    }

    public function testFromEntity(): void
    {
        $o = new MyParam([]);

        $entity = $this->getMockInstanceMap(Entity::class, [
            '__get' => [
                ['name', 'bar'],
                ['status', '0'],
            ]
        ]);

        $o->fromEntity($entity);
        $this->assertEquals('bar', $o->getName());
        $this->assertEquals('0', $o->getStatus());
    }

    public function testFromEntityParent(): void
    {
        $o = new MyParam2([
            'name' => '',
            'status' => '',
        ]);

        $entity = $this->getMockInstanceMap(Entity::class, [
            '__get' => [
                ['name', 'bar'],
                ['status', '0'],
            ]
        ]);

        $o->fromEntity($entity);
        $this->assertEmpty($o->name);
        $this->assertEmpty($o->status);
    }

    public function testMagicGetSnake(): void
    {
        $o = new MyParam3([
            'name' => '',
            'foo_bar' => 'baz',
        ]);

        $this->assertEquals($o->foo_bar, 'baz');
        $this->assertEquals($o->fooBar, 'baz');
    }
}
