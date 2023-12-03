<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Migration\Seed\Command;

use Platine\Config\Config;
use Platine\Console\Application as ConsoleApp;
use Platine\Console\Input\Reader;
use Platine\Console\IO\Interactor;
use Platine\Filesystem\Adapter\Local\LocalAdapter;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Migration\Seed\Command\SeedCreateCommand;
use Platine\Test\Framework\Console\BaseCommandTestCase;
use RuntimeException;

/*
 * @group core
 * @group framework
 */
class SeedCreateCommandTest extends BaseCommandTestCase
{
    public function testExecute(): void
    {
        $seedDir = $this->createVfsDirectory('seed', $this->vfsPath);
        $seedPath = $seedDir->url();

        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);

        $writer = $this->getWriterInstance();
        $reader =  $this->getMockInstance(Reader::class);
        $application =  $this->getMockInstance(Application::class);
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer,
            'confirm' => true,
            'prompt' => 'add user seed',
        ]);
        $app = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['database.migration.seed_path', 'seeds', $seedPath],
            ]
        ]);

        $o = new SeedCreateCommand($application, $config, $filesystem);
        $o->bind($app);
        $o->parse(['platine']);
        $this->assertEquals('seed:create', $o->getName());
        $o->interact($reader, $writer);
        $o->execute();
        $seedFilename = 'AddUserSeed.php';

        $seedFile = implode(
            DIRECTORY_SEPARATOR,
            [
                'vfs://root',
                'my_tests',
                'seed',
                'AddUserSeed.php'
            ]
        );

        $expected = <<<E
SEED GENERATION

Seed detail: 
Name: add user seed
Class name: AddUserSeed
Filename: AddUserSeed.php
Path: $seedFile

Seed [add user seed] generated successfully

E;
        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
        $this->assertTrue($seedDir->hasChild($seedFilename));
    }

    public function testExecutePathDoesNotExist(): void
    {
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);

        $writer = $this->getWriterInstance();
        $reader =  $this->getMockInstance(Reader::class);
        $application =  $this->getMockInstance(Application::class);
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer,
            'confirm' => true,
            'prompt' => 'add user seed',
        ]);
        $app = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['database.migration.seed_path', 'seeds', 'path/do/not/exist'],
            ]
        ]);

        $this->expectException(RuntimeException::class);
        $o = new SeedCreateCommand($application, $config, $filesystem);
        $o->bind($app);
        $o->parse(['platine']);
        $this->assertEquals('seed:create', $o->getName());
        $o->interact($reader, $writer);
        $o->execute();
    }
}
