<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Migration\Command;

use Platine\Config\Config;
use Platine\Console\Application as ConsoleApp;
use Platine\Console\Input\Reader;
use Platine\Console\IO\Interactor;
use Platine\Database\Connection;
use Platine\Filesystem\Adapter\Local\Directory;
use Platine\Filesystem\Adapter\Local\File;
use Platine\Filesystem\Adapter\Local\LocalAdapter;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Migration\Command\MigrationExecuteCommand;
use Platine\Framework\Migration\MigrationEntity;
use Platine\Framework\Migration\MigrationRepository;
use Platine\Orm\Query\EntityQuery;
use Platine\Test\Framework\Console\BaseCommandTestCase;
use RuntimeException;

/*
 * @group core
 * @group framework
 */
class MigrationExecuteCommandTest extends BaseCommandTestCase
{
    public function testExecuteInvalidMigrationAction(): void
    {
        $migrationDir = $this->createVfsDirectory('migrations', $this->vfsPath);
        $migrationPath = $migrationDir->url();

        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);

        $writer = $this->getWriterInstance();
        $application =  $this->getMockInstance(Application::class);
        $entityQueryMiddle = $this->getMockInstance(EntityQuery::class, [
            'all' => [],
        ]);
        $entityQuery = $this->getMockInstance(EntityQuery::class, [
            'orderBy' => $entityQueryMiddle,
        ]);
        $repository =  $this->getMockInstance(MigrationRepository::class, [
            'query' => $entityQuery
        ]);
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer,
        ]);
        $app = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['database.migration.path', 'migrations', $migrationPath],
                ['database.migration.table', 'migrations', 'migrations'],
            ]
        ]);

        $this->expectException(RuntimeException::class);
        $o = new MigrationExecuteCommand($application, $repository, $config, $filesystem);
        $o->bind($app);
        $o->parse(['platine', 'dow']);
    }

    public function testExecuteUpNoMigration(): void
    {
        $migrationDir = $this->createVfsDirectory('migrations', $this->vfsPath);
        $migrationPath = $migrationDir->url();

        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);

        $writer = $this->getWriterInstance();
        $reader =  $this->getMockInstance(Reader::class);
        $application =  $this->getMockInstance(Application::class);
        $entityQueryMiddle = $this->getMockInstance(EntityQuery::class, [
            'all' => [],
        ]);
        $entityQuery = $this->getMockInstance(EntityQuery::class, [
            'orderBy' => $entityQueryMiddle,
        ]);
        $repository =  $this->getMockInstance(MigrationRepository::class, [
            'query' => $entityQuery
        ]);
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer,
        ]);
        $app = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['database.migration.path', 'migrations', $migrationPath],
                ['database.migration.table', 'migrations', 'migrations'],
            ]
        ]);

        $o = new MigrationExecuteCommand($application, $repository, $config, $filesystem);
        $o->bind($app);
        $o->parse(['platine']);
        $this->assertEquals('migration:exec', $o->getName());
        $o->interact($reader, $writer);
        $o->execute();
        $expected = 'MIGRATION EXECUTION

Migration already up to date';

        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteUpThereIsMigration(): void
    {
        $migrationDir = $this->createVfsDirectory('migrations', $this->vfsPath);
        $migrationPath = $migrationDir->url();
        $this->createMigrationTestFile($migrationDir);

        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);

        $writer = $this->getWriterInstance();
        $reader =  $this->getMockInstance(Reader::class);
        $cnx =  $this->getMockInstance(Connection::class);
        $application =  $this->getMockInstanceMap(Application::class, [
            'get' => [
                [Connection::class, $cnx]
            ]
        ]);
        $entityQueryMiddle = $this->getMockInstance(EntityQuery::class, [
            'all' => [],
        ]);
        $entityQuery = $this->getMockInstance(EntityQuery::class, [
            'orderBy' => $entityQueryMiddle,
        ]);
        $repository =  $this->getMockInstance(MigrationRepository::class, [
            'query' => $entityQuery
        ]);
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer,
        ]);
        $app = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['database.migration.path', 'migrations', $migrationPath],
                ['database.migration.table', 'migrations', 'migrations'],
            ]
        ]);

        $o = new MigrationExecuteCommand($application, $repository, $config, $filesystem);
        $o->bind($app);
        $o->parse(['platine', '-i', '20210915_100000']);
        $this->assertEquals('migration:exec', $o->getName());
        $o->interact($reader, $writer);
        $o->execute();
        $expected = 'MIGRATION EXECUTION

* Execute migration up for 20210915_100000: add user table
';

        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteUpThereIsMigrationInputVersion(): void
    {
        $migrationDir = $this->createVfsDirectory('migrations', $this->vfsPath);
        $migrationPath = $migrationDir->url();
        $this->createMigrationTestFile($migrationDir);

        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);

        $writer = $this->getWriterInstance();
        $reader =  $this->getMockInstance(Reader::class);
        $cnx =  $this->getMockInstance(Connection::class);
        $application =  $this->getMockInstanceMap(Application::class, [
            'get' => [
                [Connection::class, $cnx]
            ]
        ]);
        $entityQueryMiddle = $this->getMockInstance(EntityQuery::class, [
            'all' => [],
        ]);
        $entityQuery = $this->getMockInstance(EntityQuery::class, [
            'orderBy' => $entityQueryMiddle,
        ]);
        $repository =  $this->getMockInstance(MigrationRepository::class, [
            'query' => $entityQuery
        ]);
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer,
            'choice' => '20210915_100000',
        ]);
        $app = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['database.migration.path', 'migrations', $migrationPath],
                ['database.migration.table', 'migrations', 'migrations'],
            ]
        ]);

        $o = new MigrationExecuteCommand($application, $repository, $config, $filesystem);
        $o->bind($app);
        $o->parse(['platine']);
        $this->assertEquals('migration:exec', $o->getName());
        $o->interact($reader, $writer);
        $o->execute();
        $expected = 'MIGRATION EXECUTION

* Execute migration up for 20210915_100000: add user table
';

        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteUpThereIsMigrationInvalidVersion(): void
    {
        $migrationDir = $this->createVfsDirectory('migrations', $this->vfsPath);
        $migrationPath = $migrationDir->url();
        $this->createMigrationTestFile($migrationDir);

        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);

        $writer = $this->getWriterInstance();
        $reader =  $this->getMockInstance(Reader::class);
        $cnx =  $this->getMockInstance(Connection::class);
        $application =  $this->getMockInstanceMap(Application::class, [
            'get' => [
                [Connection::class, $cnx]
            ]
        ]);
        $entityQueryMiddle = $this->getMockInstance(EntityQuery::class, [
            'all' => [],
        ]);
        $entityQuery = $this->getMockInstance(EntityQuery::class, [
            'orderBy' => $entityQueryMiddle,
        ]);
        $repository =  $this->getMockInstance(MigrationRepository::class, [
            'query' => $entityQuery
        ]);
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer,
            'choice' => '20210915_100001',
        ]);
        $app = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['database.migration.path', 'migrations', $migrationPath],
                ['database.migration.table', 'migrations', 'migrations'],
            ]
        ]);

        $o = new MigrationExecuteCommand($application, $repository, $config, $filesystem);
        $o->bind($app);
        $o->parse(['platine']);
        $this->assertEquals('migration:exec', $o->getName());
        $o->interact($reader, $writer);
        $o->execute();
        $expected = 'MIGRATION EXECUTION

Invalid migration version [20210915_100001] or already executed';

        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteDownNoMigration(): void
    {
        $migrationDir = $this->createVfsDirectory('migrations', $this->vfsPath);
        $migrationPath = $migrationDir->url();

        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);

        $writer = $this->getWriterInstance();
        $reader =  $this->getMockInstance(Reader::class);
        $application =  $this->getMockInstance(Application::class);
        $entityQueryMiddle = $this->getMockInstance(EntityQuery::class, [
            'all' => [],
        ]);
        $entityQuery = $this->getMockInstance(EntityQuery::class, [
            'orderBy' => $entityQueryMiddle,
        ]);
        $repository =  $this->getMockInstance(MigrationRepository::class, [
            'query' => $entityQuery
        ]);
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer,
        ]);
        $app = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['database.migration.path', 'migrations', $migrationPath],
                ['database.migration.table', 'migrations', 'migrations'],
            ]
        ]);

        $o = new MigrationExecuteCommand($application, $repository, $config, $filesystem);
        $o->bind($app);
        $o->parse(['platine', 'down']);
        $this->assertEquals('migration:exec', $o->getName());
        $o->interact($reader, $writer);
        $o->execute();
        $expected = 'MIGRATION EXECUTION

No migration to rollback';

        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteDownThereIsMigration(): void
    {
        $migrationDir = $this->createVfsDirectory('migrations', $this->vfsPath);
        $migrationPath = $migrationDir->url();
        $this->createMigrationTestFile($migrationDir);

        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);

        $entity = $this->getMockInstanceMap(MigrationEntity::class, [
            '__get' => [
                ['version', '20210915_100000'],
                ['description', 'add user table'],
            ]
        ]);
        $writer = $this->getWriterInstance();
        $reader =  $this->getMockInstance(Reader::class);
        $cnx =  $this->getMockInstance(Connection::class);
        $application =  $this->getMockInstanceMap(Application::class, [
            'get' => [
                [Connection::class, $cnx]
            ]
        ]);
        $entityQueryMiddle = $this->getMockInstance(EntityQuery::class, [
            'all' => [$entity],
        ]);
        $entityQuery = $this->getMockInstance(EntityQuery::class, [
            'orderBy' => $entityQueryMiddle,
        ]);
        $repository =  $this->getMockInstance(MigrationRepository::class, [
            'query' => $entityQuery
        ]);
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer,
        ]);
        $app = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['database.migration.path', 'migrations', $migrationPath],
                ['database.migration.table', 'migrations', 'migrations'],
            ]
        ]);

        $o = new MigrationExecuteCommand($application, $repository, $config, $filesystem);
        $o->bind($app);
        $o->parse(['platine', 'down', '-i', '20210915_100000']);
        $this->assertEquals('migration:exec', $o->getName());
        $o->interact($reader, $writer);
        $o->execute();
        $expected = 'MIGRATION EXECUTION

* Execute migration down for 20210915_100000: add user table
';

        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteDownThereIsMigrationInputVersion(): void
    {
        $migrationDir = $this->createVfsDirectory('migrations', $this->vfsPath);
        $migrationPath = $migrationDir->url();
        $this->createMigrationTestFile($migrationDir);

        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);

        $entity = $this->getMockInstanceMap(MigrationEntity::class, [
            '__get' => [
                ['version', '20210915_100000'],
                ['description', 'add user table'],
            ]
        ]);
        $writer = $this->getWriterInstance();
        $reader =  $this->getMockInstance(Reader::class);
        $cnx =  $this->getMockInstance(Connection::class);
        $application =  $this->getMockInstanceMap(Application::class, [
            'get' => [
                [Connection::class, $cnx]
            ]
        ]);
        $entityQueryMiddle = $this->getMockInstance(EntityQuery::class, [
            'all' => [$entity],
        ]);
        $entityQuery = $this->getMockInstance(EntityQuery::class, [
            'orderBy' => $entityQueryMiddle,
        ]);
        $repository =  $this->getMockInstance(MigrationRepository::class, [
            'query' => $entityQuery,
            'findBy' => $entity,
        ]);
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer,
            'choice' => '20210915_100000',
        ]);
        $app = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['database.migration.path', 'migrations', $migrationPath],
                ['database.migration.table', 'migrations', 'migrations'],
            ]
        ]);

        $o = new MigrationExecuteCommand($application, $repository, $config, $filesystem);
        $o->bind($app);
        $o->parse(['platine', 'down']);
        $this->assertEquals('migration:exec', $o->getName());
        $o->interact($reader, $writer);
        $o->execute();
        $expected = 'MIGRATION EXECUTION

* Execute migration down for 20210915_100000: add user table
';

        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteDownThereIsMigrationInvalidVersion(): void
    {
        $migrationDir = $this->createVfsDirectory('migrations', $this->vfsPath);
        $migrationPath = $migrationDir->url();
        $this->createMigrationTestFile($migrationDir);

        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);

        $entity = $this->getMockInstanceMap(MigrationEntity::class, [
            '__get' => [
                ['version', '20210915_100000'],
                ['description', 'add user table'],
            ]
        ]);
        $writer = $this->getWriterInstance();
        $reader =  $this->getMockInstance(Reader::class);
        $cnx =  $this->getMockInstance(Connection::class);
        $application =  $this->getMockInstanceMap(Application::class, [
            'get' => [
                [Connection::class, $cnx]
            ]
        ]);
        $entityQueryMiddle = $this->getMockInstance(EntityQuery::class, [
            'all' => [$entity],
        ]);
        $entityQuery = $this->getMockInstance(EntityQuery::class, [
            'orderBy' => $entityQueryMiddle,
        ]);
        $repository =  $this->getMockInstance(MigrationRepository::class, [
            'query' => $entityQuery
        ]);
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer,
            'choice' => '20210915_100001',
        ]);
        $app = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['database.migration.path', 'migrations', $migrationPath],
                ['database.migration.table', 'migrations', 'migrations'],
            ]
        ]);

        $o = new MigrationExecuteCommand($application, $repository, $config, $filesystem);
        $o->bind($app);
        $o->parse(['platine', 'down']);
        $this->assertEquals('migration:exec', $o->getName());
        $o->interact($reader, $writer);
        $o->execute();
        $expected = 'MIGRATION EXECUTION

Invalid migration version [20210915_100001] or not yet executed';

        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteInvalidMigrationClassName(): void
    {
        $migrationDir = $this->createVfsDirectory('migrations', $this->vfsPath);
        $migrationPath = $migrationDir->url();
        $this->createInvalidMigrationClassTestFile($migrationDir);

        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);

        $writer = $this->getWriterInstance();
        $reader =  $this->getMockInstance(Reader::class);
        $cnx =  $this->getMockInstance(Connection::class);
        $application =  $this->getMockInstanceMap(Application::class, [
            'get' => [
                [Connection::class, $cnx]
            ]
        ]);
        $entityQueryMiddle = $this->getMockInstance(EntityQuery::class, [
            'all' => [],
        ]);
        $entityQuery = $this->getMockInstance(EntityQuery::class, [
            'orderBy' => $entityQueryMiddle,
        ]);
        $repository =  $this->getMockInstance(MigrationRepository::class, [
            'query' => $entityQuery
        ]);
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer,
            'choice' => '20210915_110',
        ]);
        $app = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['database.migration.path', 'migrations', $migrationPath],
                ['database.migration.table', 'migrations', 'migrations'],
            ]
        ]);
        $this->expectException(RuntimeException::class);
        $o = new MigrationExecuteCommand($application, $repository, $config, $filesystem);
        $o->bind($app);
        $o->parse(['platine', 'up']);
        $this->assertEquals('migration:exec', $o->getName());
        $o->interact($reader, $writer);
        $o->execute();
    }

    public function testExecuteMigrationFileDoesNotExist(): void
    {
        $migrationDir = $this->createVfsDirectory('migrations', $this->vfsPath);
        $migrationPath = $migrationDir->url();

        $fileMigration = $this->getMockInstance(File::class, [
            'getName' => '20210915_100000_add_user_table.php'
        ]);
        $file = $this->getMockInstance(File::class, [
            'exists' => false
        ]);
        $directory = $this->getMockInstance(Directory::class, [
            'exists' => true,
            'isWritable' => true,
            'read' => [$fileMigration],
        ]);
        $filesystem = $this->getMockInstance(Filesystem::class, [
            'file' => $file,
            'directory' => $directory,
        ]);

        $writer = $this->getWriterInstance();
        $reader =  $this->getMockInstance(Reader::class);
        $cnx =  $this->getMockInstance(Connection::class);
        $application =  $this->getMockInstanceMap(Application::class, [
            'get' => [
                [Connection::class, $cnx]
            ]
        ]);
        $entityQueryMiddle = $this->getMockInstance(EntityQuery::class, [
            'all' => [],
        ]);
        $entityQuery = $this->getMockInstance(EntityQuery::class, [
            'orderBy' => $entityQueryMiddle,
        ]);
        $repository =  $this->getMockInstance(MigrationRepository::class, [
            'query' => $entityQuery
        ]);
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer,
            'choice' => '20210915_100000',
        ]);
        $app = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['database.migration.path', 'migrations', $migrationPath],
                ['database.migration.table', 'migrations', 'migrations'],
            ]
        ]);
        $this->expectException(RuntimeException::class);
        $o = new MigrationExecuteCommand($application, $repository, $config, $filesystem);
        $o->bind($app);
        $o->parse(['platine', 'up']);
        $this->assertEquals('migration:exec', $o->getName());
        $o->interact($reader, $writer);
        $o->execute();
    }

    private function createMigrationTestFile($migrationDir)
    {
        $this->createVfsFile(
            '20210915_100000_add_user_table.php',
            $migrationDir,
            '<?php
        namespace Platine\Framework\Migration;

        use Platine\Framework\Migration\AbstractMigration;

        class AddUserTable20210915100000 extends AbstractMigration
        {

            public function up(): void
            {
              //Action when migrate up

            }

            public function down(): void
            {
              //Action when migrate down

            }
        }'
        );
    }

    private function createInvalidMigrationClassTestFile($migrationDir)
    {
        $this->createVfsFile(
            '20210915_110_add_user_table.php',
            $migrationDir,
            '<?php
        namespace Platine\Framework\Migration;

        use Platine\Framework\Migration\AbstractMigration;

        class AddUserTable20210915100001 extends AbstractMigration
        {

            public function up(): void
            {
              //Action when migrate up

            }

            public function down(): void
            {
              //Action when migrate down

            }
        }'
        );
    }
}
