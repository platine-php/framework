<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Tool\Database;

use org\bovigo\vfs\vfsStream;
use Platine\Database\Configuration;
use Platine\Database\Connection;
use Platine\Database\Schema;
use Platine\Dev\PlatineTestCase;
use Platine\Filesystem\Adapter\Local\File;
use Platine\Filesystem\Filesystem;
use Platine\Framework\Tool\Database\DatabaseDump;
use Platine\Logger\Logger;
use RuntimeException;

/*
 * @group core
 * @group framework
 */
class DatabaseDumpTest extends PlatineTestCase
{
    public function testConstruct(): void
    {
        global $mock_function_exists_to_true, $mock_function_exists_to_false;
        $mock_function_exists_to_true = true;

        $cnx = $this->getMockInstance(Connection::class);
        $logger = $this->getMockInstance(Logger::class);
        $filesystem = $this->getMockInstance(Filesystem::class);
        $o = new DatabaseDump($cnx, $logger, $filesystem);

        $this->assertInstanceOf(DatabaseDump::class, $o);
        $this->assertFalse($o->isCompress());
        $o->setCompress(true);
        $this->assertTrue($o->isCompress());
        $this->assertNull($o->getOnProgress());
        $o->setOnProgress(fn() => 1);
        $this->assertIsCallable($o->getOnProgress());
        $mock_function_exists_to_false = true;
        $this->expectException(RuntimeException::class);
        $o->setCompress(true);
    }

    public function testBackupFileNotWritable(): void
    {
        $file = $this->getMockInstance(File::class, [
            'exists' => true,
            'isWritable' => false,
        ]);
        $cnx = $this->getMockInstance(Connection::class);
        $logger = $this->getMockInstance(Logger::class);
        $filesystem = $this->getMockInstance(Filesystem::class, [
            'file' => $file,
        ]);
        $o = new DatabaseDump($cnx, $logger, $filesystem);

        $this->expectException(RuntimeException::class);
        $o->backup('filename');
    }

    public function testBackup(): void
    {
        global $mock_function_exists_to_true,
               $mock_gzencode_to_value;
        $mock_function_exists_to_true = true;
        $mock_gzencode_to_value = 'dumpgzencode';

        $vfsRoot = vfsStream::setup();
        $dumpPath = $this->createVfsDirectory('dbdump', $vfsRoot);
        $dumpFile = $this->createVfsFile('dbbackup.sql', $dumpPath);

        $dbconfig = $this->getMockInstance(Configuration::class, [
            'getDriverName' => '',
        ]);

        $schema = $this->getMockInstance(Schema::class, [
            'getDatabaseName' => 'foodb',
            'getTables' => ['footable' => 'footable'],
            'getViews' => ['fooview' => 'fooview'],
        ]);

        $cnx = $this->getMockInstance(Connection::class, [
            'getConfig' => $dbconfig,
            'getSchema' => $schema,
        ]);
        $logger = $this->getMockInstance(Logger::class);
        $filesystem = $this->getMockInstance(Filesystem::class);
        $o = new DatabaseDump($cnx, $logger, $filesystem);
        $o->setTables(['footable' => DatabaseDump::NONE]);
        $o->setCompress(true);

        $this->expectMethodCallCount($filesystem, 'file', 1);
        $this->expectMethodCallCount($cnx, 'getSchema', 1);
        $o->backup($dumpFile->url());
    }

    public function testRestoreFileNotExist(): void
    {
        $file = $this->getMockInstance(File::class, [
            'exists' => false,
            'isWritable' => false,
        ]);
        $cnx = $this->getMockInstance(Connection::class);
        $logger = $this->getMockInstance(Logger::class);
        $filesystem = $this->getMockInstance(Filesystem::class, [
            'file' => $file,
        ]);
        $o = new DatabaseDump($cnx, $logger, $filesystem);

        $this->expectException(RuntimeException::class);
        $o->restore('filename');
    }

    public function testRestore(): void
    {
        global $mock_function_exists_to_true,
               $mock_gzencode_to_value,
               $mock_gzdecode_to_value;
        $mock_function_exists_to_true = true;
        $mock_gzencode_to_value = 'dumpgzencode';
        $mock_gzdecode_to_value = 'dumpgzdecode';

        $file = $this->getMockInstance(File::class, [
            'exists' => true,
            'isReadable' => true,
        ]);
        $cnx = $this->getMockInstance(Connection::class);
        $logger = $this->getMockInstance(Logger::class);
        $filesystem = $this->getMockInstance(Filesystem::class, [
            'file' => $file,
        ]);
        $o = new DatabaseDump($cnx, $logger, $filesystem);
        $o->setCompress(true);

        $this->expectMethodCallCount($file, 'read');
        $o->restore('filename');
    }
}
