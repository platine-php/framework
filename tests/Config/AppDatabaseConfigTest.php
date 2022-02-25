<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Config;

use InvalidArgumentException;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Config\AppDatabaseConfig;
use Platine\Framework\Config\DatabaseConfigLoader;
use Platine\Framework\Config\Model\Configuration;
use stdClass;

/*
 * @group core
 * @group framework
 */
class AppDatabaseConfigTest extends PlatineTestCase
{
    public function testConstructor(): void
    {

        $loader = $this->getMockInstance(DatabaseConfigLoader::class);

        $o = new AppDatabaseConfig($loader);

        $this->assertInstanceOf(DatabaseConfigLoader::class, $o->getLoader());
        $this->assertEquals($loader, $o->getLoader());
    }

    public function testSetGetLoader(): void
    {

        $loader = $this->getMockInstance(DatabaseConfigLoader::class);
        $loaderSet = $this->getMockInstance(DatabaseConfigLoader::class);

        $o = new AppDatabaseConfig($loader);

        $this->assertInstanceOf(DatabaseConfigLoader::class, $o->getLoader());
        $this->assertEquals($loader, $o->getLoader());

        $o->setLoader($loaderSet);
        $this->assertInstanceOf(DatabaseConfigLoader::class, $o->getLoader());
        $this->assertEquals($loaderSet, $o->getLoader());
    }

    public function testSetGetEnvironment(): void
    {

        $loader = $this->getMockInstance(DatabaseConfigLoader::class);

        $o = new AppDatabaseConfig($loader);


        $o->setEnvironment('prod');
        $this->assertEquals('prod', $o->getEnvironment());
    }

    public function testGetConfig(): void
    {

        $loader = $this->getMockInstance(DatabaseConfigLoader::class, [
            'load' => [
                'tva' => 19.6
            ],
            'all' => [new stdClass()]
        ]);

        $o = new AppDatabaseConfig($loader);

        $res = $o->get('app.tva');
        $this->assertTrue($o->has('app.tva'));
        $this->assertTrue(isset($o['app.tva']));
        $this->assertEquals(19.6, $res);
        $this->assertEquals(19.6, $o['app.tva']);
        $this->assertNotEmpty($o->getItems());
        $this->assertIsArray($o->getItems());
        $this->assertCount(1, $o->getItems());
        $this->assertCount(1, $o->all());
    }

    public function testSetConfigNew(): void
    {

        $loader = $this->getMockInstance(DatabaseConfigLoader::class, [
            'loadConfig' => null
        ]);

        $loader->expects($this->exactly(1))
                ->method('insertConfig');

        $o = new AppDatabaseConfig($loader);

        $o->set('app.tva', 19.6);
    }

    public function testSetConfigUpdate(): void
    {
        $entity = $this->getMockInstanceMap(Configuration::class, [
            '__get' => [
                ['name', 'tva'],
                ['type', 'double'],
                ['value', '19.6'],
            ]
        ]);

        $loader = $this->getMockInstance(DatabaseConfigLoader::class, [
            'loadConfig' => $entity
        ]);

        $loader->expects($this->exactly(3))
                ->method('updateConfig');

        $o = new AppDatabaseConfig($loader);

        $o->set('app.tva', 19.6);
        $o['app.permissions'] = ['add', 'delete'];
        unset($o['app.tva']);
    }

    public function testInvalidKey(): void
    {

        $loader = $this->getMockInstance(DatabaseConfigLoader::class, [
            'load' => [
                'tva' => 19.6
            ]
        ]);

        $o = new AppDatabaseConfig($loader);

        $this->expectException(InvalidArgumentException::class);
        $o->get('tva');
    }
}
