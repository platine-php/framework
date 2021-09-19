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
use Platine\Framework\Migration\Command\MigrationStatusCommand;
use Platine\Framework\Migration\MigrationEntity;
use Platine\Framework\Migration\MigrationRepository;
use Platine\Orm\Query\EntityQuery;
use Platine\Test\Framework\Console\BaseCommandTestCase;

/*
 * @group core
 * @group framework
 */
class MigrationStatusCommandTest extends BaseCommandTestCase
{


    public function testExecute(): void
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
        $cnx =  $this->getMockInstance(Connection::class);
        $application =  $this->getMockInstanceMap(Application::class, [
            'get' => [
                [Connection::class, $cnx],
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

        $o = new MigrationStatusCommand($application, $repository, $config, $filesystem);
        $o->bind($app);
        $o->parse(['platine']);
        $this->assertEquals('migration:status', $o->getName());
        $o->execute();
        $expected = 'MIGRATION STATUS

Migration path: ' . $migrationPath . DIRECTORY_SEPARATOR . '
Migration table: migrations
Migration All: 1
Migration Available: 1
Migration Executed: 1

MIGRATION LIST
+-----------------+----------------+------+--------+
| Version         | Description    | Date | Status |
+-----------------+----------------+------+--------+
| 20210915_100000 | add user table |      | UP     |
| 20210916_100000 | add role table |      | DOWN   |
+-----------------+----------------+------+--------+

Command finished successfully
';

        $this->assertEquals($expected, $this->getConsoleOutputContent());
    }

    private function createMigrationTestFile($migrationDir)
    {
        $this->createVfsFile(
            '20210916_100000_add_role_table.php',
            $migrationDir,
            '<?php
        namespace Platine\Framework\Migration;

        use Platine\Framework\Migration\AbstractMigration;

        class AddRoleTable20210916100000 extends AbstractMigration
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
