<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Console\Command;

use Platine\Console\Application as ConsoleApp;
use Platine\Console\IO\Interactor;
use Platine\Filesystem\Adapter\Local\LocalAdapter;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Config\AppDatabaseConfig;
use Platine\Framework\Config\DatabaseConfigLoader;
use Platine\Framework\Console\Command\MakeDatabaseConfigCommand;
use Platine\Orm\Entity;
use Platine\Test\Framework\Console\BaseCommandTestCase;

/*
 * @group core
 * @group framework
 */
class MakeDatabaseConfigCommandTest extends BaseCommandTestCase
{
    public function testExecuteDefault(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $actionName = 'Config/' . 'MyAppConfig';
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, [
            'getNamespace' => 'MyApp\\',
            'getAppPath' => $dir->url()
        ]);

        $cfgEntity = $this->getMockInstanceMap(Entity::class, [
            '__get' => [
                ['module', 'app'],
                ['name', 'foo'],
                ['type', 'string'],
            ]
        ]);

        $dbLoader = $this->getMockInstance(DatabaseConfigLoader::class, [
            'all' => [$cfgEntity],
        ]);
        $dbConfig = $this->getMockInstance(AppDatabaseConfig::class, [
            'getLoader' => $dbLoader,
        ]);

        $this->createInputContent('y');

        $reader = $this->getReaderInstance();
        $writer = $this->getWriterInstance();

        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer,
            'reader' => $reader,
            'confirm' => true,
        ]);

        $consoleApp = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);

        $o = new MakeDatabaseConfigCommand($app, $filesystem, $dbConfig);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:dbconfig', $o->getName());

        $o->interact($reader, $writer);
        $o->execute();
        $classPath = implode(
            DIRECTORY_SEPARATOR,
            [
                'vfs://root',
                'app',
                'Config',
                'MyAppConfig.php'
            ]
        );
        $expected = <<<E
GENERATION OF NEW CLASS

Generation of new database config class [MyApp\Config\MyAppConfig]

Class: MyApp\Config\MyAppConfig
Path: $classPath
Namespace: MyApp\Config
Class [MyApp\Config\MyAppConfig] generated successfully.

E;
        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }



    public function testGetClassTemplate(): void
    {
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, []);
        $dbConfig = $this->getMockInstance(AppDatabaseConfig::class, [
        ]);

        $o = new MakeDatabaseConfigCommand($app, $filesystem, $dbConfig);

        $this->assertNotEmpty($o->getClassTemplate());
    }
}
