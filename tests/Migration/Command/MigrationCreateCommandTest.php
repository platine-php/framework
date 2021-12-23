<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Migration\Command;

use Platine\Config\Config;
use Platine\Console\Application as ConsoleApp;
use Platine\Console\Input\Reader;
use Platine\Console\IO\Interactor;
use Platine\Filesystem\Adapter\Local\LocalAdapter;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Migration\Command\MigrationCreateCommand;
use Platine\Framework\Migration\MigrationRepository;
use Platine\Test\Framework\Console\BaseCommandTestCase;
use RuntimeException;

/*
 * @group core
 * @group framework
 */
class MigrationCreateCommandTest extends BaseCommandTestCase
{
    public function testExecute(): void
    {
        global $mock_date_to_sample;
        $mock_date_to_sample = true;

        $migrationDir = $this->createVfsDirectory('migrations', $this->vfsPath);
        $migrationPath = $migrationDir->url();

        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);

        $writer = $this->getWriterInstance();
        $reader =  $this->getMockInstance(Reader::class);
        $application =  $this->getMockInstance(Application::class);
        $repository =  $this->getMockInstance(MigrationRepository::class);
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer,
            'confirm' => true,
            'prompt' => 'add user table',
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

        $o = new MigrationCreateCommand($application, $repository, $config, $filesystem);
        $o->bind($app);
        $o->parse(['platine']);
        $this->assertEquals('migration:create', $o->getName());
        $o->interact($reader, $writer);
        $o->execute();
        $migrationFilename = '20210915_100000_add_user_table.php';
        $migrationFile = $migrationPath . '/' . $migrationFilename;
        $expected = 'MIGRATION GENERATION

Migration detail: 
Name: add user table
Version: 20210915_100000
Class name: AddUserTable20210915100000
Filename: 20210915_100000_add_user_table.php
Path: ' . $migrationFile . '

Migration [add user table] generated successfully
';
        $this->assertEquals($expected, $this->getConsoleOutputContent());
        $this->assertTrue($migrationDir->hasChild($migrationFilename));
    }

    public function testExecutePathDoesNotExist(): void
    {
        global $mock_date_to_sample;
        $mock_date_to_sample = true;

        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);

        $writer = $this->getWriterInstance();
        $reader =  $this->getMockInstance(Reader::class);
        $application =  $this->getMockInstance(Application::class);
        $repository =  $this->getMockInstance(MigrationRepository::class);
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer,
            'confirm' => true,
            'prompt' => 'add user table',
        ]);
        $app = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['database.migration.path', 'migrations', 'path/do/not/exist'],
                ['database.migration.table', 'migrations', 'migrations'],
            ]
        ]);

        $this->expectException(RuntimeException::class);
        $o = new MigrationCreateCommand($application, $repository, $config, $filesystem);
        $o->bind($app);
        $o->parse(['platine']);
        $this->assertEquals('migration:create', $o->getName());
        $o->interact($reader, $writer);
        $o->execute();
    }
}
