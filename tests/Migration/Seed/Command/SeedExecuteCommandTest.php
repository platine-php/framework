<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Migration\Seed\Command;

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
use Platine\Framework\Migration\Seed\Command\SeedExecuteCommand;
use Platine\Test\Framework\Console\BaseCommandTestCase;
use RuntimeException;

/*
 * @group core
 * @group framework
 */
class SeedExecuteCommandTest extends BaseCommandTestCase
{
    public function testExecuteNoSeedAvailable(): void
    {
        $seedDir = $this->createVfsDirectory('migrations', $this->vfsPath);
        $seedPath = $seedDir->url();

        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);

        $writer = $this->getWriterInstance();
        $reader =  $this->getMockInstance(Reader::class);
        $application =  $this->getMockInstance(Application::class);
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer,
        ]);
        $app = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['database.migration.seed_path', 'seeds', $seedPath],
            ]
        ]);

        $o = new SeedExecuteCommand($application, $config, $filesystem);
        $o->bind($app);
        $o->parse(['platine']);
        $this->assertEquals('seed:exec', $o->getName());
        $o->interact($reader, $writer);
        $o->execute();
        $expected = 'SEED EXECUTION

No seed available for execution
Command finished successfully
';

        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteThereIsSeed(): void
    {
        $seedDir = $this->createVfsDirectory('seeds', $this->vfsPath);
        $seedPath = $seedDir->url();
        $this->createSeedTestFile($seedDir);

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
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer,
            'choices' => ['1'],
        ]);
        $app = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['database.migration.seed_path', 'seeds', $seedPath],
            ]
        ]);

        $o = new SeedExecuteCommand($application, $config, $filesystem);
        $o->bind($app);
        $o->parse(['platine']);
        $this->assertEquals('seed:exec', $o->getName());
        $o->interact($reader, $writer);
        $o->execute();
        $expected = 'SEED EXECUTION

* Execute seed for [add user]

Command finished successfully
';

        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }


    public function testExecuteInvalidSeedClassName(): void
    {
        $seedDir = $this->createVfsDirectory('seeds', $this->vfsPath);
        $seedPath = $seedDir->url();
        $this->createInvalidSeedClassTestFile($seedDir);

        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);

        $writer = $this->getWriterInstance();
        $reader =  $this->getMockInstance(Reader::class);
        $application =  $this->getMockInstanceMap(Application::class, [
        ]);
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer,
            'choices' => ['1']
        ]);
        $app = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['database.migration.seed_path', 'seeds', $seedPath],
            ]
        ]);
        $this->expectException(RuntimeException::class);
        $o = new SeedExecuteCommand($application, $config, $filesystem);
        $o->bind($app);
        $o->parse(['platine']);
        $this->assertEquals('seed:exec', $o->getName());
        $o->interact($reader, $writer);
        $o->execute();
    }

    public function testExecuteFileDoesNotExist(): void
    {
        $seedDir = $this->createVfsDirectory('seeds', $this->vfsPath);
        $seedPath = $seedDir->url();

        $fileSeed = $this->getMockInstance(File::class, [
            'getName' => 'AddUserSeed.php'
        ]);
        $file = $this->getMockInstance(File::class, [
            'exists' => false
        ]);
        $directory = $this->getMockInstance(Directory::class, [
            'exists' => true,
            'isWritable' => true,
            'read' => [$fileSeed],
        ]);
        $filesystem = $this->getMockInstance(Filesystem::class, [
            'file' => $file,
            'directory' => $directory,
        ]);

        $writer = $this->getWriterInstance();
        $reader =  $this->getMockInstance(Reader::class);
        $application =  $this->getMockInstanceMap(Application::class, [
        ]);
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer,
            'choices' => ['1'],
        ]);
        $app = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['database.migration.seed_path', 'seeds', $seedPath],
            ]
        ]);
        $this->expectException(RuntimeException::class);
        $o = new SeedExecuteCommand($application, $config, $filesystem);
        $o->bind($app);
        $o->parse(['platine']);
        $this->assertEquals('seed:exec', $o->getName());
        $o->interact($reader, $writer);
        $o->execute();
    }

    private function createSeedTestFile($seedDir)
    {
        $this->createVfsFile(
            'AddUserSeed.php',
            $seedDir,
            '<?php
        namespace Platine\Framework\Migration\Seed;

        use Platine\Framework\Migration\Seed\AbstractSeed;

        class AddUserSeed extends AbstractSeed
        {

            public function run(): void
            {
              //Action when run seed

            }
        }'
        );
    }

    private function createInvalidSeedClassTestFile($seedDir)
    {
        $this->createVfsFile(
            'UpdateUserSeed.php',
            $seedDir,
            '<?php
        namespace Platine\Framework\Migration\Seed;

        use Platine\Framework\Migration\Seed\AbstractSeed;

        class AddUserWrongSeed extends AbstractSeed
        {

            public function run(): void
            {
              //Action when run seed

            }
        }'
        );
    }
}
