<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Console\Command;

use Platine\Console\Application as ConsoleApp;
use Platine\Console\IO\Interactor;
use Platine\Filesystem\Adapter\Local\LocalAdapter;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Auth\Entity\User;
use Platine\Framework\Auth\Repository\UserRepository;
use Platine\Framework\Console\Command\MakeResourceActionCommand;
use Platine\Test\Framework\Console\BaseCommandTestCase;
use Platine\Test\Framework\Fixture\MyParam;
use Platine\Test\Framework\Fixture\MyValidator;

/*
 * @group core
 * @group framework
 */
class MakeResourceActionCommandTest extends BaseCommandTestCase
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

        $this->createInputContent('Foo\Bar\Not\Found');
        $this->createInputContent("\n");
        $this->createInputContent(MyParam::class);
        $this->createInputContent("\n");
        $this->createInputContent('Foo\Bar\Not\Found');
        $this->createInputContent("\n");
        $this->createInputContent(MyValidator::class);
        $this->createInputContent("\n");
        $this->createInputContent('Foo\Bar\Not\Found');
        $this->createInputContent("\n");
        $this->createInputContent(User::class);
        $this->createInputContent("\n");
        $this->createInputContent('Foo\Bar\Not\Found');
        $this->createInputContent("\n");
        $this->createInputContent(UserRepository::class);
        $this->createInputContent("\n");
        $this->createInputContent(Application::class);
        $this->createInputContent("\n");
        $this->createInputContent('Foo\Bar\Not\Found');
        $this->createInputContent('');

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

        $o = new MakeResourceActionCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:resource', $o->getName());

        $o->interact($reader, $writer);
        $o->execute();
        $expected = 'GENERATION OF NEW CLASS

Enter the form parameter full class name: Class does not exists, please enter the form parameter full class name: Enter the form validator full class name: Class does not exists, please enter the form validator full class name: Enter the entity full class name: Class does not exists, please enter the entity full class name: Enter the repository full class name: Class does not exists, please enter the repository full class name: Enter the properties list (empty value to finish):
Property full class name: Property full class name: The class [Foo\Bar\Not\Found] does not exists
Property full class name: Generation of new resource class [MyApp\actions\MyAction]

Class: MyApp\actions\MyAction
Path: vfs://root/app/actions/MyAction.php
Namespace: MyApp\actions
';
        $this->assertEquals($expected, $this->getConsoleOutputContent());
    }


    public function testExecuteCreateSuccess(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $actionName = 'actions/' . 'MyAction';
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, [
            'getNamespace' => 'MyApp\\',
            'getAppPath' => $dir->url()
        ]);

        $this->createInputContent(MyParam::class);
        $this->createInputContent("\n");
        $this->createInputContent(MyValidator::class);
        $this->createInputContent("\n");
        $this->createInputContent(User::class);
        $this->createInputContent("\n");
        $this->createInputContent(UserRepository::class);
        $this->createInputContent("\n");
        $this->createInputContent('');
        $this->createInputContent("\n");
        $this->createInputContent('y');

        $reader = $this->getReaderInstance();
        $writer = $this->getWriterInstance();

        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer,
            'reader' => $reader
        ], [
            'prompt',
            'confirm',
        ]);

        $this->setPropertyValue(Interactor::class, $interactor, 'reader', $reader);
        $this->setPropertyValue(Interactor::class, $interactor, 'writer', $writer);

        $consoleApp = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);

        $o = new MakeResourceActionCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName, '-i=name:name,description', '-c=name:name,description', '-o=name:desc,description']);
        $this->assertEquals('make:resource', $o->getName());

        $o->interact($reader, $writer);
        $o->execute();
        $expected = 'GENERATION OF NEW CLASS

Enter the form parameter full class name: Enter the form validator full class name: Enter the entity full class name: Enter the repository full class name: Enter the properties list (empty value to finish):
Property full class name: Generation of new resource class [MyApp\actions\MyAction]

Class: MyApp\actions\MyAction
Path: vfs://root/app/actions/MyAction.php
Namespace: MyApp\actions
Class [MyApp\actions\MyAction] generated successfully.
';
        $this->assertEquals($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteCreateFromJsonConfig(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $json = $this->createVfsFile('config.json', $dir, "{\"message_create\": \"Category created\"}");
        $actionName = 'actions/' . 'MyAction';
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, [
            'getNamespace' => 'MyApp\\',
            'getAppPath' => $dir->url()
        ]);

        $this->createInputContent(MyParam::class);
        $this->createInputContent("\n");
        $this->createInputContent(MyValidator::class);
        $this->createInputContent("\n");
        $this->createInputContent(User::class);
        $this->createInputContent("\n");
        $this->createInputContent(UserRepository::class);
        $this->createInputContent("\n");
        $this->createInputContent('');
        $this->createInputContent("\n");
        $this->createInputContent('y');

        $reader = $this->getReaderInstance();
        $writer = $this->getWriterInstance();

        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer,
            'reader' => $reader
        ], [
            'prompt',
            'confirm',
        ]);

        $this->setPropertyValue(Interactor::class, $interactor, 'reader', $reader);
        $this->setPropertyValue(Interactor::class, $interactor, 'writer', $writer);

        $consoleApp = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);

        $o = new MakeResourceActionCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName, '-j=' . $json->url()]);
        $this->assertEquals('make:resource', $o->getName());

        $o->interact($reader, $writer);
        $o->execute();
        $expected = 'GENERATION OF NEW CLASS

Enter the form parameter full class name: Enter the form validator full class name: Enter the entity full class name: Enter the repository full class name: Enter the properties list (empty value to finish):
Property full class name: Generation of new resource class [MyApp\actions\MyAction]

Class: MyApp\actions\MyAction
Path: vfs://root/app/actions/MyAction.php
Namespace: MyApp\actions
Class [MyApp\actions\MyAction] generated successfully.
';
        $this->assertEquals($expected, $this->getConsoleOutputContent());
    }

    public function testGetPropertyNameNotExist(): void
    {
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, []);

        $o = new MakeResourceActionCommand($app, $filesystem);

        $this->assertEmpty($this->runPrivateProtectedMethod($o, 'getPropertyName', ['not_found_prop']));
    }

    public function testGetClassTemplate(): void
    {
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, []);

        $o = new MakeResourceActionCommand($app, $filesystem);

        $this->assertNotEmpty($o->getClassTemplate());
    }
}
