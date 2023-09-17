<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Console\Command;

use Platine\Console\Application as ConsoleApp;
use Platine\Console\IO\Interactor;
use Platine\Filesystem\Adapter\Local\LocalAdapter;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Console\Command\MakeActionCommand;
use Platine\Test\Framework\Console\BaseCommandTestCase;
use stdClass;

/*
 * @group core
 * @group framework
 */
class MakeActionCommandTest extends BaseCommandTestCase
{
    public function testExecuteDefault(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $actionName = 'actions/' . 'MyAction';
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

        $o = new MakeActionCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:action', $o->getName());

        // Only to coverage tests
        $this->assertEquals('MakeActionCommandTest', $o->getClassBaseName($this));
        $this->assertEquals(stdClass::class, $o->getClassBaseName(stdClass::class));

        $o->interact($reader, $writer);
        $o->execute();
        $expected = 'GENERATION OF NEW CLASS

Enter the properties list (empty value to finish):
Generation of new action class [MyApp\actions\MyAction]

Class: MyApp\actions\MyAction
Path: vfs://root/app/actions/MyAction.php
Namespace: MyApp\actions
';
        $this->assertEquals($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteNameFromInput(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $actionName = 'actions/' . 'MyAction';
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, [
            'getNamespace' => 'MyApp\\',
            'getAppPath' => $dir->url()
        ]);

        $this->createInputContent($actionName);

        $reader = $this->getReaderInstance();
        $writer = $this->getWriterInstance();

        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer,
            'reader' => $reader
        ], [
            'prompt',
        ]);

        $this->setPropertyValue(Interactor::class, $interactor, 'reader', $reader);
        $this->setPropertyValue(Interactor::class, $interactor, 'writer', $writer);

        $consoleApp = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);

        $o = new MakeActionCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine']);
        $this->assertEquals('make:action', $o->getName());

        $o->interact($reader, $writer);
        $o->execute();
        $expected = 'GENERATION OF NEW CLASS

Enter the full class name (can include root namespace): Enter the properties list (empty value to finish):
Property full class name: Generation of new action class [MyApp\actions\MyAction]

Class: MyApp\actions\MyAction
Path: vfs://root/app/actions/MyAction.php
Namespace: MyApp\actions
';
        $this->assertEquals($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteWithProperties(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $actionName = 'actions/' . 'MyAction';
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, [
            'getNamespace' => 'MyApp\\',
            'getAppPath' => $dir->url()
        ]);

        $this->createInputContent('Platine\Framework\App\Application');
        $this->createInputContent("\n");
        $this->createInputContent('Platine\Template\Template');
        $this->createInputContent("\n");
        $this->createInputContent('');


        $reader = $this->getReaderInstance();
        $writer = $this->getWriterInstance();

        $interactor = $this->getMockInstance(
            Interactor::class,
            [
            'writer' => $writer,
            'reader' => $reader
            ],
            [
             'prompt',
            'confirm',
            'choice',
            'isValidChoice',
            ]
        );

        $this->setPropertyValue(Interactor::class, $interactor, 'reader', $reader);
        $this->setPropertyValue(Interactor::class, $interactor, 'writer', $writer);

        $consoleApp = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);

        $o = new MakeActionCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:action', $o->getName());

        $o->interact($reader, $writer);
        $o->execute();
        $expected = 'GENERATION OF NEW CLASS

Enter the properties list (empty value to finish):
Property full class name: Property full class name: Property full class name: Generation of new action class [MyApp\actions\MyAction]

Class: MyApp\actions\MyAction
Path: vfs://root/app/actions/MyAction.php
Namespace: MyApp\actions
Are you confirm the generation of [MyApp\actions\MyAction] ?Class [MyApp\actions\MyAction] generated successfully.
';
        $this->assertEquals($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteWithPropertiesClassNotExist(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $actionName = 'actions/' . 'MyAction';
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, [
            'getNamespace' => 'MyApp\\',
            'getAppPath' => $dir->url()
        ]);

        $this->createInputContent('Foo\Bar\Not\Found');
        $this->createInputContent('');


        $reader = $this->getReaderInstance();
        $writer = $this->getWriterInstance();

        $interactor = $this->getMockInstance(
            Interactor::class,
            [
            'writer' => $writer,
            'reader' => $reader
            ],
            [
             'prompt',
            ]
        );

        $this->setPropertyValue(Interactor::class, $interactor, 'reader', $reader);
        $this->setPropertyValue(Interactor::class, $interactor, 'writer', $writer);

        $consoleApp = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);

        $o = new MakeActionCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:action', $o->getName());

        $o->interact($reader, $writer);
        $o->execute();
        $expected = 'GENERATION OF NEW CLASS

Enter the properties list (empty value to finish):
Property full class name: The class [Foo\Bar\Not\Found] does not exists
Property full class name: Generation of new action class [MyApp\actions\MyAction]

Class: MyApp\actions\MyAction
Path: vfs://root/app/actions/MyAction.php
Namespace: MyApp\actions
';
        $this->assertEquals($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteFileAlreadyExists(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $this->createVfsFile('MyAction.php', $dir);
        $actionName = 'MyAction';

        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, [
            'getNamespace' => 'MyApp\\',
            'getAppPath' => $dir->url()
        ]);

        $reader = $this->getReaderInstance();
        $writer = $this->getWriterInstance();

        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer,
            'reader' => $reader
        ]);

        $consoleApp = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);

        $o = new MakeActionCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);

        $this->createInputContent('');

        $o->interact($reader, $writer);
        $o->execute();
        $expected = 'GENERATION OF NEW CLASS

Enter the properties list (empty value to finish):
Generation of new action class [MyApp\MyAction]

File [vfs://root/app/MyAction.php] already exists.
';
        $this->assertEquals($expected, $this->getConsoleOutputContent());
    }

    public function testGetClassTemplate(): void
    {
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, []);

        $o = new MakeActionCommand($app, $filesystem);

        $this->assertNotEmpty($o->getClassTemplate());
    }
}
