<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Console\Command;

use Platine\Console\Application as ConsoleApp;
use Platine\Console\IO\Interactor;
use Platine\Filesystem\Adapter\Local\LocalAdapter;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Console\Command\MakeEnumCommand;
use Platine\Test\Framework\Console\BaseCommandTestCase;

/*
 * @group core
 * @group framework
 */
class MakeEnumCommandTest extends BaseCommandTestCase
{
    public function testExecuteDefault(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $actionName = 'Enum/' . 'MyEnum';
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

        $o = new MakeEnumCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:enum', $o->getName());

        $o->interact($reader, $writer);
        $o->execute();
        $expected = 'GENERATION OF NEW CLASS' . PHP_EOL .
PHP_EOL .
'Enter the enumeration list (empty value to finish):' . PHP_EOL .
'Generation of new enum class [MyApp\Enum\MyEnum]' . PHP_EOL .
PHP_EOL .
'Class: MyApp\Enum\MyEnum' . PHP_EOL .
'Path: vfs://root/app/Enum/MyEnum.php' . PHP_EOL .
'Namespace: MyApp\Param' . PHP_EOL . ' ' . PHP_EOL;

        $this->assertEquals($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteWithEntityInstance(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $actionName = 'Enum/' . 'MyEnum';
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

        $o = new MakeEnumCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:enum', $o->getName());

        $o->interact($reader, $writer);
        $o->execute();
        $expected = 'GENERATION OF NEW CLASS

Enter the properties list (empty value to finish):
Property name: Property name: Entity field name for [code] (just enter to ignore):'
        . ' Generation of new form parameter class [MyApp\Enum\MyEnum]

Class: MyApp\Enum\MyEnum
Path: vfs://root/app/Enum/MyEnum.php
Namespace: MyApp\Param
Class [MyApp\Enum\MyEnum] generated successfully.
';
        $this->assertEquals($expected, $this->getConsoleOutputContent());
    }

    public function testGetClassTemplate(): void
    {
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, []);

        $o = new MakeEnumCommand($app, $filesystem);

        $this->assertNotEmpty($o->getClassTemplate());
    }

    public function testGetUsesContentNoEntityInstance(): void
    {
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, []);

        $o = new MakeEnumCommand($app, $filesystem);

        $result = $this->runPrivateProtectedMethod($o, 'getUsesContent');

        $this->assertEmpty($result);
    }
}
