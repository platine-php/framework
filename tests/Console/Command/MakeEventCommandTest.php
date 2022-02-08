<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Console\Command;

use Platine\Console\Application as ConsoleApp;
use Platine\Console\IO\Interactor;
use Platine\Filesystem\Adapter\Local\LocalAdapter;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Console\Command\MakeEventCommand;
use Platine\Test\Framework\Console\BaseCommandTestCase;

/*
 * @group core
 * @group framework
 */
class MakeEventCommandTest extends BaseCommandTestCase
{
    public function testExecuteDefault(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $actionName = 'Event/' . 'MyEvent';
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

        $o = new MakeEventCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:event', $o->getName());

        $o->interact($reader, $writer);
        $o->execute();
        $expected = 'GENERATION OF NEW CLASS

Generation of new event class [MyApp\Event\MyEvent]

Class: MyApp\Event\MyEvent
Path: vfs://root/app/Event/MyEvent.php
Namespace: MyApp\Event
';
        $this->assertEquals($expected, $this->getConsoleOutputContent());
    }


    public function testGetClassTemplate(): void
    {
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, []);

        $o = new MakeEventCommand($app, $filesystem);

        $this->assertNotEmpty($o->getClassTemplate());
    }

    public function testGetUsesContent(): void
    {
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, []);

        $o = new MakeEventCommand($app, $filesystem);

        $result = $this->runPrivateProtectedMethod($o, 'getUsesContent');

        $this->assertEmpty($result);
    }
}
