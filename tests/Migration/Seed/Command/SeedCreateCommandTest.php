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
        global $mock_date_to_sample_seed;
        $mock_date_to_sample_seed = true;

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
        $seedFilename = '20210915_100000_add_user_seed.php';

        $seedFile = implode(
            DIRECTORY_SEPARATOR,
            [
                'vfs://root',
                'my_tests',
                'seed',
                '20210915_100000_add_user_seed.php'
            ]
        );

        $expected = <<<E
SEED GENERATION

Seed detail: 
Name: add user seed
Version: 20210915_100000
Class name: AddUserSeed20210915100000
Filename: 20210915_100000_add_user_seed.php
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
            'prompt' => 'add user',
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
