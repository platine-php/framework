<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Migration\Command;

use Platine\Config\Config;
use Platine\Console\Application as ConsoleApp;
use Platine\Console\Input\Reader;
use Platine\Console\IO\Interactor;
use Platine\Database\Connection;
use Platine\Filesystem\Adapter\Local\LocalAdapter;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Migration\Command\MigrationExecuteCommand;
use Platine\Framework\Migration\Command\MigrationResetCommand;
use Platine\Framework\Migration\MigrationEntity;
use Platine\Framework\Migration\MigrationRepository;
use Platine\Orm\Query\EntityQuery;
use Platine\Test\Framework\Console\BaseCommandTestCase;

/*
 * @group core
 * @group framework
 */
class MigrationResetCommandTest extends BaseCommandTestCase
{
    public function testExecuteNoMigration(): void
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

        $o = new MigrationResetCommand($application, $repository, $config, $filesystem);
        $o->bind($app);
        $o->parse(['platine']);
        $this->assertEquals('migration:reset', $o->getName());
        $o->interact($reader, $writer);
        $o->execute();
        $expected = 'ALL MIGRATION ROLLBACK

No migration done before';

        $this->assertEquals($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteThereIsMigration(): void
    {
        $migrationDir = $this->createVfsDirectory('migrations', $this->vfsPath);
        $migrationPath = $migrationDir->url();

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
        $execCmd =  $this->getMockInstance(MigrationExecuteCommand::class);
        $application =  $this->getMockInstanceMap(Application::class, [
            'get' => [
                [Connection::class, $cnx],
                [MigrationExecuteCommand::class, $execCmd]
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
            'confirm' => true,
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

        $o = new MigrationResetCommand($application, $repository, $config, $filesystem);
        $o->bind($app);
        $o->parse(['platine']);
        $this->assertEquals('migration:reset', $o->getName());
        $o->interact($reader, $writer);
        $o->execute();
        $expected = 'ALL MIGRATION ROLLBACK

Migration list to be rollback:
 * 20210915_100000 - add user table


Migration rollback successfully
';

        $this->assertEquals($expected, $this->getConsoleOutputContent());
    }
}
