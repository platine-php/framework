<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Job;

use Exception;
use org\bovigo\vfs\vfsStream;
use Platine\Config\Config;
use Platine\Dev\PlatineTestCase;
use Platine\Filesystem\Adapter\Local\Directory;
use Platine\Filesystem\Adapter\Local\File;
use Platine\Filesystem\Filesystem;
use Platine\Framework\Job\CleanTempFileTask;
use Platine\Logger\Logger;

class CleanTempFileTaskTest extends PlatineTestCase
{
    public function testMapEntity(): void
    {
        $filesystem = $this->getMockInstance(Filesystem::class);
        $config = $this->getMockInstance(Config::class);
        $logger = $this->getMockInstance(Logger::class);
        $o = new CleanTempFileTask($filesystem, $config, $logger);

        $this->assertInstanceOf(CleanTempFileTask::class, $o);


        $this->assertEquals($o->name(), 'clean temp files');
        $this->assertEquals($o->expression(), '0 3 * * *');
    }

    public function testRun(): void
    {
        $vfsRoot = vfsStream::setup();
        $tmpDir = $this->createVfsDirectory('tmp', $vfsRoot);
        $tmpFile = $this->createVfsFile('tmp.png', $tmpDir, 'foocontentpng');


        $filesystem = new Filesystem();
        $config = $this->getMockInstance(Config::class, [
            'get' => $tmpDir->url(),
        ]);
        $logger = $this->getMockInstance(Logger::class);
        $o = new CleanTempFileTask($filesystem, $config, $logger);

        $this->assertTrue($tmpDir->hasChildren($tmpFile));
        $tmpFile->lastModified(time() - (86400 * 10));
        $o->run();
        $this->assertFalse($tmpDir->hasChildren($tmpFile));
    }

    public function testRunError(): void
    {
        $vfsRoot = vfsStream::setup();
        $tmpDir = $this->createVfsDirectory('tmp', $vfsRoot);

        $file = $this->getMockInstance(File::class, [
            'getMtime' => 1039089,
        ]);
        $dir = $this->getMockInstance(Directory::class, [
            'read' => [$file],
        ]);

        $filesystem = $this->getMockInstance(Filesystem::class, [
            'directory' => $dir,
        ]);

        $file->expects($this->any())
             ->method('delete')
             ->willThrowException(new Exception());

        $config = $this->getMockInstance(Config::class, [
            'get' => $tmpDir->url(),
        ]);
        $logger = $this->getMockInstance(Logger::class);
        $o = new CleanTempFileTask($filesystem, $config, $logger);

        $this->expectMethodCallCount($logger, 'error');
        $o->run();
    }
}
