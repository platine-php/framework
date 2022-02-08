<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Console\Command;

use Platine\Console\Application as ConsoleApp;
use Platine\Console\IO\Interactor;
use Platine\Filesystem\Adapter\Local\LocalAdapter;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Console\Command\MakeListenerCommand;
use Platine\Test\Framework\Console\BaseCommandTestCase;

/*
 * @group core
 * @group framework
 */
class MakeListenerCommandTest extends BaseCommandTestCase
{
    public function testExecuteDefault(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $actionName = 'Listener/' . 'MyListener';
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, [
            'getNamespace' => 'MyApp\\',
            'getAppPath' => $dir->url()
        ]);

        $this->createInputContent('Platine\Framework\App\Application');

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

        $o = new MakeListenerCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:listener', $o->getName());

        $o->interact($reader, $writer);
        $o->execute();
        $expected = 'GENERATION OF NEW CLASS

Enter the event full class name: Generation of new event listener class [MyApp\Listener\MyListener]

Class: MyApp\Listener\MyListener
Path: vfs://root/app/Listener/MyListener.php
Namespace: MyApp\Listener
Class [MyApp\Listener\MyListener] generated successfully.
';
        $this->assertEquals($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteEventClassNotExists(): void
    {
        $this->createInputContent('Foo\Bar\Not\Found');
        $this->createInputContent("\n");
        $this->createInputContent('Platine\Framework\App\Application');

        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $actionName = 'Listener/' . 'MyListener';
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, [
            'getNamespace' => 'MyApp\\',
            'getAppPath' => $dir->url()
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

        $o = new MakeListenerCommand($app, $filesystem);

        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:listener', $o->getName());

        $o->interact($reader, $writer);

        $o->execute();
        $expected = 'GENERATION OF NEW CLASS

Enter the event full class name: Class does not exists, please enter the event '
        . 'full class name: Generation of new event listener class [MyApp\Listener\MyListener]

Class: MyApp\Listener\MyListener
Path: vfs://root/app/Listener/MyListener.php
Namespace: MyApp\Listener
Class [MyApp\Listener\MyListener] generated successfully.
';
        $this->assertEquals($expected, $this->getConsoleOutputContent());
    }


    public function testGetClassTemplate(): void
    {
        $this->createInputContent('Platine\Framework\App\Application');

        $actionName = 'Listener/' . 'MyListener';

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

        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, []);

        $o = new MakeListenerCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:listener', $o->getName());

        $o->interact($reader, $writer);

        $this->assertNotEmpty($o->getClassTemplate());
    }

    public function testGetUsesContent(): void
    {
        $this->createInputContent('Platine\Framework\App\Application');

        $actionName = 'Listener/' . 'MyListener';

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

        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, []);

        $o = new MakeListenerCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:listener', $o->getName());

        $o->interact($reader, $writer);

        $result = $this->runPrivateProtectedMethod($o, 'getUsesContent');

        $this->assertNotEmpty($result);
    }
}
