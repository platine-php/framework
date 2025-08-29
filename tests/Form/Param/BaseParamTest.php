<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Form\Param;

use Platine\Container\Container;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Config\AppDatabaseConfig;
use Platine\Http\ServerRequest;
use Platine\Http\ServerRequestInterface;
use Platine\Orm\Entity;
use Platine\Test\Framework\Fixture\MyParam;
use Platine\Test\Framework\Fixture\MyParam2;
use Platine\Test\Framework\Fixture\MyParam3;
use Platine\Test\Framework\Fixture\MyParam4;
use stdClass;

/*
 * @group core
 * @group framework
 */
class BaseParamTest extends PlatineTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        // Restore queries
        $this->addContainerData();
    }

    public function testConstructor(): void
    {
        $this->addContainerData();

        $o = new MyParam([]);
        $this->assertCount(0, $o->getDefault());
    }

    public function testConstructorWithQueries(): void
    {
        $this->addContainerData(['status' => '10']);

        $o = new MyParam([]);
        $this->assertCount(1, $o->getDefault());
        $this->assertEquals(10, $o->status);
    }

    public function testFromConfig(): void
    {
        $o = new MyParam([]);
        $cfg = $this->getMockInstance(AppDatabaseConfig::class);
        $o->fromConfig($cfg);
        $this->assertCount(0, $o->getDefault());
    }

    public function testLoad(): void
    {
        $o = new MyParam([
            'name' => ' foo',
            'status' => '1'
        ]);
        $this->assertEquals('foo', $o->getName());
        $this->assertEquals('1', $o->getStatus());

        //Using magic method
        $this->assertEquals('foo', $o->name);
        $this->assertEquals('1', $o->status);
        $this->assertNull($o->undefined);
    }

    public function testLoadValueTypeNotFound(): void
    {
        $o = new MyParam4([
            'obj' => new stdClass(),
            'name' => 1,
            'age' => '',
        ]);
        $this->assertEmpty($o->getDefault());

        //Using magic method
        $this->assertEquals(1, $o->name);
        $this->assertNull($o->age);
        $this->assertInstanceOf(stdClass::class, $o->obj);
    }

    public function testDataAndJson(): void
    {
        $o = new MyParam([
            'name' => 'foo',
            'status' => '1'
        ]);
        $data = $o->data();
        $this->assertCount(2, $data);
        $this->assertEquals('foo', $data['name']);
        $this->assertEquals('1', $data['status']);

        $this->assertEquals('{"name":"foo","status":"1"}', json_encode($o));
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

    private function addContainerData(array $queries = []): void
    {
        $sr = (new ServerRequest())
                ->withParsedBody([])
                ->withQueryParams($queries);

        $c = Container::getInstance();
        $c->instance($sr, ServerRequestInterface::class);
    }
}
