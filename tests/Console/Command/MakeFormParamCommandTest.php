<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Console\Command;

use Platine\Console\Application as ConsoleApp;
use Platine\Console\IO\Interactor;
use Platine\Filesystem\Adapter\Local\LocalAdapter;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Console\Command\MakeFormParamCommand;
use Platine\Test\Framework\Console\BaseCommandTestCase;

/*
 * @group core
 * @group framework
 */
class MakeFormParamCommandTest extends BaseCommandTestCase
{
    public function testExecuteDefault(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $actionName = 'Param/' . 'MyParam';
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

        $o = new MakeFormParamCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:param', $o->getName());

        $o->interact($reader, $writer);
        $o->execute();
        $expected = 'GENERATION OF NEW CLASS

Enter the properties list (empty value to finish):
Generation of new form parameter class [MyApp\Param\MyParam]

Class: MyApp\Param\MyParam
Path: vfs://root/app/Param/MyParam.php
Namespace: MyApp\Param
';
        $this->assertEquals($expected, $this->getConsoleOutputContent());
    }


    public function testExecuteDefaultWithCustomDataType(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $actionName = 'Param/' . 'MyParam';
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, [
            'getNamespace' => 'MyApp\\',
            'getAppPath' => $dir->url()
        ]);

        $this->createInputContent('code:int:true:1');
        $this->createInputContent('sexe:string:false:');
        $this->createInputContent('amount:float:false');
        $this->createInputContent("\n");
        $this->createInputContent("\n");
        $this->createInputContent('y');
        $this->createInputContent('code');
        $this->createInputContent("\n");


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
            ]
        );

        $this->setPropertyValue(Interactor::class, $interactor, 'reader', $reader);
        $this->setPropertyValue(Interactor::class, $interactor, 'writer', $writer);

        $consoleApp = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);

        $o = new MakeFormParamCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:param', $o->getName());

        $o->interact($reader, $writer);
        $o->execute();
        $expected = 'GENERATION OF NEW CLASS

Enter the properties list (empty value to finish):
Property name: Property name: Entity field name for [code] (just enter to ignore):'
        . ' Generation of new form parameter class [MyApp\Param\MyParam]

Class: MyApp\Param\MyParam
Path: vfs://root/app/Param/MyParam.php
Namespace: MyApp\Param
Class [MyApp\Param\MyParam] generated successfully.
';
        $this->assertEquals($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteWithEntityInstance(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $actionName = 'Param/' . 'MyParam';
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, [
            'getNamespace' => 'MyApp\\',
            'getAppPath' => $dir->url()
        ]);

        $this->createInputContent('code');
        $this->createInputContent("\n");
        $this->createInputContent("\n");
        $this->createInputContent('y');
        $this->createInputContent('code');
        $this->createInputContent("\n");


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
            ]
        );

        $this->setPropertyValue(Interactor::class, $interactor, 'reader', $reader);
        $this->setPropertyValue(Interactor::class, $interactor, 'writer', $writer);

        $consoleApp = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);

        $o = new MakeFormParamCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:param', $o->getName());

        $o->interact($reader, $writer);
        $o->execute();
        $expected = 'GENERATION OF NEW CLASS

Enter the properties list (empty value to finish):
Property name: Property name: Entity field name for [code] (just enter to ignore):'
        . ' Generation of new form parameter class [MyApp\Param\MyParam]

Class: MyApp\Param\MyParam
Path: vfs://root/app/Param/MyParam.php
Namespace: MyApp\Param
Class [MyApp\Param\MyParam] generated successfully.
';
        $this->assertEquals($expected, $this->getConsoleOutputContent());
    }

    public function testGetClassTemplate(): void
    {
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, []);

        $o = new MakeFormParamCommand($app, $filesystem);

        $this->assertNotEmpty($o->getClassTemplate());
    }

    public function testGetUsesContentNoEntityInstance(): void
    {
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, []);

        $o = new MakeFormParamCommand($app, $filesystem);

        $result = $this->runPrivateProtectedMethod($o, 'getUsesContent');

        $this->assertEmpty($result);
    }
}
