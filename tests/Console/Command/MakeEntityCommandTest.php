<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Console\Command;

use Platine\Console\Application as ConsoleApp;
use Platine\Console\IO\Interactor;
use Platine\Filesystem\Adapter\Local\LocalAdapter;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Console\Command\MakeEntityCommand;
use Platine\Test\Framework\Console\BaseCommandTestCase;

/*
 * @group core
 * @group framework
 */
class MakeEntityCommandTest extends BaseCommandTestCase
{
    public function testExecuteDefault(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $actionName = 'Entity/' . 'MyEntity';
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, [
            'getNamespace' => 'MyApp\\',
            'getAppPath' => $dir->url()
        ]);

        $this->createInputContent('');

        $reader = $this->getReaderInstance();
        $writer = $this->getWriterInstance();

        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer,
            'reader' => $reader
        ]);

        $consoleApp = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);

        $o = new MakeEntityCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:entity', $o->getName());

        $o->interact($reader, $writer);
        $o->execute();
        $classPath = implode(
            DIRECTORY_SEPARATOR,
            [
                'vfs://root',
                'app',
                'Entity',
                'MyEntity.php'
            ]
        );
        $expected = <<<E
GENERATION OF NEW CLASS

Generation of new entity class [MyApp\Entity\MyEntity]

Class: MyApp\Entity\MyEntity
Path: $classPath
Namespace: MyApp\Entity

E;
        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteWithTimestamp(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);

        $actionName = 'Entity/' . 'MyEntity';
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, [
            'getNamespace' => 'MyApp\\',
            'getAppPath' => $dir->url(),
        ]);


        $reader = $this->getReaderInstance();
        $writer = $this->getWriterInstance();

        $interactor = $this->getMockInstance(
            Interactor::class,
            [
            'writer' => $writer,
            'reader' => $reader
            ],
            [
             'confirm',
             'prompt',
            ]
        );

        $this->setPropertyValue(Interactor::class, $interactor, 'reader', $reader);
        $this->setPropertyValue(Interactor::class, $interactor, 'writer', $writer);

        $consoleApp = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);

        $o = new MakeEntityCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:entity', $o->getName());

        $o->interact($reader, $writer);
        $o->execute();
        $classPath = implode(
            DIRECTORY_SEPARATOR,
            [
                'vfs://root',
                'app',
                'Entity',
                'MyEntity.php'
            ]
        );
        $expected = <<<E
GENERATION OF NEW CLASS

Created at field name [created_at]: Updated at field name [updated_at]: Generation of new entity class [MyApp\Entity\MyEntity]

Class: MyApp\Entity\MyEntity
Path: $classPath
Namespace: MyApp\Entity
Class [MyApp\Entity\MyEntity] generated successfully.

E;
        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteWithTimestampCustomFields(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $actionName = 'Entity/' . 'MyEntity';
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, [
            'getNamespace' => 'MyApp\\',
            'getAppPath' => $dir->url()
        ]);

        $this->createInputContent('create_date');
        $this->createInputContent('update_date');

        $reader = $this->getReaderInstance();
        $writer = $this->getWriterInstance();

        $interactor = $this->getMockInstance(
            Interactor::class,
            [
            'writer' => $writer,
            'reader' => $reader
            ],
            [
             'confirm',
             'prompt',
            ]
        );

        $this->setPropertyValue(Interactor::class, $interactor, 'reader', $reader);
        $this->setPropertyValue(Interactor::class, $interactor, 'writer', $writer);

        $consoleApp = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);

        $o = new MakeEntityCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:entity', $o->getName());

        $o->interact($reader, $writer);
        $o->execute();
        $classPath = implode(
            DIRECTORY_SEPARATOR,
            [
                'vfs://root',
                'app',
                'Entity',
                'MyEntity.php'
            ]
        );
        $expected = <<<E
GENERATION OF NEW CLASS

Created at field name [created_at]: Updated at field name [updated_at]: Generation of new entity class [MyApp\Entity\MyEntity]

Class: MyApp\Entity\MyEntity
Path: $classPath
Namespace: MyApp\Entity
Class [MyApp\Entity\MyEntity] generated successfully.

E;
        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }

    public function testGetClassTemplate(): void
    {
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, []);

        $o = new MakeEntityCommand($app, $filesystem);

        $this->assertNotEmpty($o->getClassTemplate());
    }
}
