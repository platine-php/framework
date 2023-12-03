<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Migration\Command;

use Platine\Config\Config;
use Platine\Console\Application as ConsoleApp;
use Platine\Console\IO\Interactor;
use Platine\Database\Connection;
use Platine\Database\Schema;
use Platine\Filesystem\Adapter\Local\LocalAdapter;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Migration\Command\MigrationInitCommand;
use Platine\Framework\Migration\MigrationRepository;
use Platine\Test\Framework\Console\BaseCommandTestCase;

/*
 * @group core
 * @group framework
 */
class MigrationInitCommandTest extends BaseCommandTestCase
{
    public function testExecuteAlreadyCreateTable(): void
    {
        $migrationDir = $this->createVfsDirectory('migrations', $this->vfsPath);
        $migrationPath = $migrationDir->url();

        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);

        $writer = $this->getWriterInstance();

        $application =  $this->getMockInstance(Application::class);
        $repository =  $this->getMockInstance(MigrationRepository::class, [
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
        $schema =  $this->getMockInstance(Schema::class, [
            'hasTable' => true
        ]);

        $o = new MigrationInitCommand($application, $repository, $schema, $config, $filesystem);
        $o->bind($app);
        $o->parse(['platine']);
        $o->execute();
        $expected = 'MIGRATION INITIALIZATION
Migration table [migrations] already created';
        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteCreateTableSuccess(): void
    {
        $migrationDir = $this->createVfsDirectory('migrations', $this->vfsPath);
        $migrationPath = $migrationDir->url();

        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);

        $writer = $this->getWriterInstance();

        $application =  $this->getMockInstance(Application::class);
        $repository =  $this->getMockInstance(MigrationRepository::class, [
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
        $cnx =  $this->getMockInstance(Connection::class);
        $schema =  $this->getMockInstance(
            Schema::class,
            [
                'hasTable' => false
            ],
            ['create']
        );
        $this->setPropertyValue(Schema::class, $schema, 'connection', $cnx);

        $o = new MigrationInitCommand($application, $repository, $schema, $config, $filesystem);
        $o->bind($app);
        $o->parse(['platine']);
        $o->execute();
        $expected = 'MIGRATION INITIALIZATION
Migration table [migrations] created successfully';
        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }
}
