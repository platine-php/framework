<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Console\Command;

use Platine\Console\Application as ConsoleApp;
use Platine\Console\IO\Interactor;
use Platine\Filesystem\Adapter\Local\LocalAdapter;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Console\Command\MakeRepositoryCommand;
use Platine\Test\Framework\Console\BaseCommandTestCase;

/*
 * @group core
 * @group framework
 */
class MakeRepositoryCommandTest extends BaseCommandTestCase
{
    public function testExecuteDefault(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $actionName = 'Repository/' . 'MyRepository';
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

        $o = new MakeRepositoryCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:repository', $o->getName());

        $o->interact($reader, $writer);
        $o->execute();
        $expected = 'GENERATION OF NEW CLASS

Enter the entity full class name: Generation of new repository class [MyApp\Repository\MyRepository]

Class: MyApp\Repository\MyRepository
Path: vfs://root/app/Repository/MyRepository.php
Namespace: MyApp\Repository
Class [MyApp\Repository\MyRepository] generated successfully.
';
        $this->assertEquals($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteEntityClassNotExists(): void
    {
        $this->createInputContent('Foo\Bar\Not\Found');
        $this->createInputContent("\n");
        $this->createInputContent('Platine\Framework\App\Application');

        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $actionName = 'Repository/' . 'MyRepository';
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

        $o = new MakeRepositoryCommand($app, $filesystem);

        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:repository', $o->getName());

        $o->interact($reader, $writer);

        $o->execute();
        $expected = 'GENERATION OF NEW CLASS

Enter the entity full class name: Class does not exists, please enter the entity'
        . ' full class name: Generation of new repository class [MyApp\Repository\MyRepository]

Class: MyApp\Repository\MyRepository
Path: vfs://root/app/Repository/MyRepository.php
Namespace: MyApp\Repository
Class [MyApp\Repository\MyRepository] generated successfully.
';
        $this->assertEquals($expected, $this->getConsoleOutputContent());
    }


    public function testGetClassTemplate(): void
    {
        $this->createInputContent('Platine\Framework\App\Application');

        $actionName = 'Repository/' . 'MyRepository';

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

        $o = new MakeRepositoryCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:repository', $o->getName());

        $o->interact($reader, $writer);

        $this->assertNotEmpty($o->getClassTemplate());
    }

    public function testGetUsesContent(): void
    {
        $this->createInputContent('Platine\Framework\App\Application');

        $actionName = 'Repository/' . 'MyRepository';

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

        $o = new MakeRepositoryCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:repository', $o->getName());

        $o->interact($reader, $writer);

        $result = $this->runPrivateProtectedMethod($o, 'getUsesContent');

        $this->assertNotEmpty($result);
    }
}
