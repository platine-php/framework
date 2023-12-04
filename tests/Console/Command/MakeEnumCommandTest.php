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
        $actionName = 'Enum/MyEnum';
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, [
            'getNamespace' => 'MyApp\\',
            'getAppPath' => $dir->url()
        ]);

        $this->createInputContent('name');
        $this->createInputContent("\n");
        $this->createInputContent("\n");
        $this->createInputContent('y');
        $this->createInputContent('A');
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

        $classPath = implode(
            DIRECTORY_SEPARATOR,
            [
                'vfs://root',
                'app',
                'Enum',
                'MyEnum.php'
            ]
        );

        $expected = <<<E
GENERATION OF NEW CLASS

Enter the enumeration list (empty value to finish):
Enum name: Enum name: Enumeration value for [NAME]: Generation of new enum class [MyApp\Enum\MyEnum]

Class: MyApp\Enum\MyEnum
Path: $classPath
Namespace: MyApp\Enum
Class [MyApp\Enum\MyEnum] generated successfully.

E;

        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }

    public function testExecute(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $actionName = 'Enum/' . 'MyEnum';
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, [
            'getNamespace' => 'MyApp\\',
            'getAppPath' => $dir->url()
        ]);

        $this->createInputContent('name_');
        $this->createInputContent("\n");
        $this->createInputContent("\n");
        $this->createInputContent('y');
        $this->createInputContent('A');
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

        $classPath = implode(
            DIRECTORY_SEPARATOR,
            [
                'vfs://root',
                'app',
                'Enum',
                'MyEnum.php'
            ]
        );

        $expected = <<<E
GENERATION OF NEW CLASS

Enter the enumeration list (empty value to finish):
Enum name: Enum name: Enumeration value for [NAME]: Generation of new enum class [MyApp\Enum\MyEnum]

Class: MyApp\Enum\MyEnum
Path: $classPath
Namespace: MyApp\Enum
Class [MyApp\Enum\MyEnum] generated successfully.

E;
        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }

    public function testGetClassTemplate(): void
    {
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, []);

        $o = new MakeEnumCommand($app, $filesystem);

        $this->assertNotEmpty($o->getClassTemplate());
    }
}
