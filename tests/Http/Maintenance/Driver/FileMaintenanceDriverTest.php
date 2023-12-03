<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http\Maintenance\Driver;

use org\bovigo\vfs\vfsStream;
use Platine\Config\Config;
use Platine\Dev\PlatineTestCase;
use Platine\Filesystem\Adapter\Local\LocalAdapter;
use Platine\Filesystem\Filesystem;
use Platine\Framework\Http\Maintenance\Driver\FileMaintenanceDriver;

/*
 * @group core
 * @group framework
 */
class FileMaintenanceDriverTest extends PlatineTestCase
{
    protected $vfsRoot;
    protected $vfsPath;

    protected function setUp(): void
    {
        parent::setUp();
        //need setup for each test
        $this->vfsRoot = vfsStream::setup();
        $this->vfsPath = vfsStream::newDirectory('my_tests')->at($this->vfsRoot);
    }

    public function testDefault(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $localAdapter = new LocalAdapter($dir->url());
        $filesystem = new Filesystem($localAdapter);

        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['maintenance.storages.file.path', '', $dir->url() . '/maintenance']
            ]
        ]);

        $o = new FileMaintenanceDriver($config, $filesystem);
        $this->assertFalse($o->active());

        $o->activate(['foo' => 'bar']);

        $this->assertTrue($o->active());

        $data = $o->data();
        $this->assertCount(1, $data);
        $this->assertArrayHasKey('foo', $data);
        $this->assertEquals('bar', $data['foo']);

        $o->deactivate();
        $this->assertFalse($o->active());
    }
}
