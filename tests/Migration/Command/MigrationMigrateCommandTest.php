<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Migration\Command;

use Platine\Config\Config;
use Platine\Console\Application as ConsoleApp;
use Platine\Console\IO\Interactor;
use Platine\Database\Connection;
use Platine\Filesystem\Adapter\Local\LocalAdapter;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Migration\Command\MigrationExecuteCommand;
use Platine\Framework\Migration\Command\MigrationMigrateCommand;
use Platine\Framework\Migration\MigrationRepository;
use Platine\Orm\Query\EntityQuery;
use Platine\Test\Framework\Console\BaseCommandTestCase;

/*
 * @group core
 * @group framework
 */
class MigrationMigrateCommandTest extends BaseCommandTestCase
{

    public function testExecuteNoMigration(): void
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

        $o = new MigrationMigrateCommand($application, $repository, $config, $filesystem);
        $o->bind($app);
        $o->parse(['platine']);
        $this->assertEquals('migration:migrate', $o->getName());
        $o->execute();
        $expected = 'MIGRATION UPGRADE TO LATEST

Migration already up to date';

        $this->assertEquals($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteMigrationSuccess(): void
    {
        $migrationDir = $this->createVfsDirectory('migrations', $this->vfsPath);
        $migrationPath = $migrationDir->url();
        $this->createMigrationTestFile($migrationDir);

        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);

        $writer = $this->getWriterInstance();
        $execCmd =  $this->getMockInstance(MigrationExecuteCommand::class);
        $application =  $this->getMockInstanceMap(Application::class, [
            'get' => [
                [MigrationExecuteCommand::class, $execCmd]
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

        $o = new MigrationMigrateCommand($application, $repository, $config, $filesystem);
        $o->bind($app);
        $o->parse(['platine']);
        $o->execute();
        $expected = 'MIGRATION UPGRADE TO LATEST

Migration list to be upgraded:
 * 20210915_100000 - add user table


Migration upgraded successfully
';

        $this->assertEquals($expected, $this->getConsoleOutputContent());
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
}
