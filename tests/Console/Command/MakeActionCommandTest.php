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
        $writer = $this->getWriterInstance();
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer
        ]);
        $consoleApp = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);

        $o = new MakeActionCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:action', $o->getName());
        $o->execute();
        $expected = 'Generation of new action class [actions\MyAction]

Class: MyApp\actions\MyAction
Path: vfs://root/app/actions/MyAction.php
Namespace: MyApp\actions
';
        $this->assertEquals($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteFileAlreadyExists(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $file = $this->createVfsFile('MyAction.php', $dir);
        $actionName = 'MyAction';
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, [
            'getNamespace' => 'MyApp\\',
            'getAppPath' => $dir->url()
        ]);
        $writer = $this->getWriterInstance();
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer
        ]);
        $consoleApp = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);

        $o = new MakeActionCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $o->execute();
        $expected = 'Generation of new action class [MyAction]

File [vfs://root/app/MyAction.php] already exists.
';
        $this->assertEquals($expected, $this->getConsoleOutputContent());
    }
}
