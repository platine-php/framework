<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Migration\Seed\Command;

use Platine\Config\Config;
use Platine\Console\Application as ConsoleApp;
use Platine\Console\IO\Interactor;
use Platine\Database\Connection;
use Platine\Filesystem\Adapter\Local\LocalAdapter;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Migration\Seed\Command\SeedStatusCommand;
use Platine\Test\Framework\Console\BaseCommandTestCase;

/*
 * @group core
 * @group framework
 */
class SeedStatusCommandTest extends BaseCommandTestCase
{
    public function testExecute(): void
    {
        $seedDir = $this->createVfsDirectory('seeds', $this->vfsPath);
        $seedPath = $seedDir->url();
        $this->createSeedTestFile($seedDir);
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);

        $writer = $this->getWriterInstance();
        $cnx =  $this->getMockInstance(Connection::class);
        $application =  $this->getMockInstanceMap(Application::class, [
            'get' => [
                [Connection::class, $cnx],
            ]
        ]);
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

        $o = new SeedStatusCommand($application, $config, $filesystem);
        $o->bind($app);
        $o->parse(['platine']);
        $this->assertEquals('seed:status', $o->getName());
        $o->execute();
        $expected = 'SEED STATUS

Seed path: ' . $seedPath . DIRECTORY_SEPARATOR . '
All seed: 1
SEED LIST
+-----+----------+
| No. | Seed     |
+-----+----------+
| 1   | add user |
+-----+----------+

Command finished successfully
';

        $this->assertEquals($expected, $this->getConsoleOutputContent());
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
}
