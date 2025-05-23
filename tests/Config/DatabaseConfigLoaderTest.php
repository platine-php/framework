<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Config;

use Platine\Database\Query\Where;
use Platine\Database\Query\WhereStatement;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Config\DatabaseConfigLoader;
use Platine\Framework\Config\Model\Configuration;
use Platine\Framework\Config\Model\ConfigurationRepository;
use Platine\Orm\Entity;
use Platine\Orm\Query\EntityQuery;

/*
 * @group core
 * @group framework
 */
class DatabaseConfigLoaderTest extends PlatineTestCase
{
    public function testConstructor(): void
    {

        $cfgRepo = $this->getMockInstance(ConfigurationRepository::class);

        $o = new DatabaseConfigLoader($cfgRepo);

        $this->assertInstanceOf(
            ConfigurationRepository::class,
            $this->getPropertyValue(DatabaseConfigLoader::class, $o, 'repository')
        );
    }

    public function testLoadNoResult(): void
    {
        $entityQueryReturn = $this->getMockInstance(EntityQuery::class, [
            'all' => [],
        ]);

        $whereStatement1 = $this->getMockInstance(WhereStatement::class, [
            'where' =>  $entityQueryReturn
        ]);

        $whereNext = $this->getMockInstance(Where::class, [
            'is' => $whereStatement1
        ]);

        $whereStatementNext = $this->getMockInstance(WhereStatement::class, [
            'where' => $whereNext
        ]);

        $whereEntityQuery = $this->getMockInstance(Where::class, [
            'is' => $whereStatementNext
        ]);

        $entityQuery = $this->getMockInstance(EntityQuery::class, [
            'where' => $whereEntityQuery,
        ]);

        $cfgRepo = $this->getMockInstance(ConfigurationRepository::class, [
            'query' => $entityQuery,
         ]);

        $cfgRepo->expects($this->any())
                    ->method('filters')
                    ->will($this->returnSelf());

        $o = new DatabaseConfigLoader($cfgRepo);

        $res = $o->load('env', 'app');

        $this->assertEmpty($res);
    }

    public function testLoadSuccess(): void
    {
        $entity = $this->getMockInstanceMap(Entity::class, [
            '__get' => [
                ['name', 'tva'],
                ['type', 'double'],
                ['value', '19.6'],
            ]
        ]);

        $entityQueryReturn = $this->getMockInstance(EntityQuery::class, [
            'all' => [
                $entity
            ],
        ]);

        $whereStatement1 = $this->getMockInstance(WhereStatement::class, [
            'where' =>  $entityQueryReturn
        ]);

        $whereNext = $this->getMockInstance(Where::class, [
            'is' => $whereStatement1
        ]);

        $whereStatementNext = $this->getMockInstance(WhereStatement::class, [
            'where' => $whereNext
        ]);

        $whereEntityQuery = $this->getMockInstance(Where::class, [
            'is' => $whereStatementNext
        ]);

        $entityQuery = $this->getMockInstance(EntityQuery::class, [
            'where' => $whereEntityQuery,
        ]);

        $cfgRepo = $this->getMockInstance(ConfigurationRepository::class, [
            'query' => $entityQuery,
        ]);

        $cfgRepo->expects($this->any())
                    ->method('filters')
                    ->will($this->returnSelf());

        $o = new DatabaseConfigLoader($cfgRepo);

        $res = $o->load('env', 'app');

        $this->assertNotEmpty($res);
        $this->assertIsArray($res);
        $this->assertCount(1, $res);
        $this->assertArrayHasKey('tva', $res);
        $this->assertIsFloat($res['tva']);
        $this->assertEquals(19.6, $res['tva']);
    }

    public function testLoadConfigNull(): void
    {

        $cfgRepo = $this->getMockInstance(ConfigurationRepository::class, [
            'findBy' => null
        ]);

        $o = new DatabaseConfigLoader($cfgRepo);

        $res = $o->loadConfig([]);
        $this->assertNull($res);
    }

    public function testLoadConfigNotNull(): void
    {
        $entity = $this->getMockInstanceMap(Configuration::class, [
            '__get' => [
                ['name', 'tva'],
                ['type', 'double'],
                ['value', '19.6'],
            ]
        ]);

        $cfgRepo = $this->getMockInstance(ConfigurationRepository::class, [
            'findBy' => $entity
        ]);

        $o = new DatabaseConfigLoader($cfgRepo);

        $res = $o->loadConfig([]);
        $this->assertInstanceOf(Entity::class, $res);
        $this->assertEquals('19.6', $res->value);
    }

    public function testInsertConfig(): void
    {
        $cfgRepo = $this->getMockInstance(ConfigurationRepository::class, [
        ]);

        $cfgRepo->expects($this->exactly(1))
                ->method('create');

        $cfgRepo->expects($this->exactly(1))
                ->method('insert');

        $o = new DatabaseConfigLoader($cfgRepo);

        $o->insertConfig([]);
    }

    public function testUpdateConfig(): void
    {
        $cfg = $this->getMockInstance(Configuration::class, [
        ]);

        $cfgRepo = $this->getMockInstance(ConfigurationRepository::class, [
            'save' => true,
        ]);

        $cfgRepo->expects($this->exactly(1))
                ->method('save');


        $o = new DatabaseConfigLoader($cfgRepo);

        $o->updateConfig($cfg);
    }

    public function testAll(): void
    {
        $cfgRepo = $this->getMockInstance(ConfigurationRepository::class, [
        ]);

        $cfgRepo->expects($this->exactly(1))
                ->method('all');


        $o = new DatabaseConfigLoader($cfgRepo);

        $o->all();
    }
}
