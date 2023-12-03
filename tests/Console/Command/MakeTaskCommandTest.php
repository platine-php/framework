<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Console\Command;

use Platine\Console\Application as ConsoleApp;
use Platine\Console\IO\Interactor;
use Platine\Filesystem\Adapter\Local\LocalAdapter;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Console\Command\MakeTaskCommand;
use Platine\Test\Framework\Console\BaseCommandTestCase;

/*
 * @group core
 * @group framework
 */
class MakeTaskCommandTest extends BaseCommandTestCase
{
    public function testExecuteDefault(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $actionName = 'Task/' . 'MyTask';
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, [
            'getNamespace' => 'MyApp\\',
            'getAppPath' => $dir->url()
        ]);

        $this->createInputContent('mytask');
        $this->createInputContent("\n");
        $this->createInputContent('* * * * *');
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

        $o = new MakeTaskCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:task', $o->getName());

        $o->interact($reader, $writer);

        $classPath = implode(
            DIRECTORY_SEPARATOR,
            [
                'vfs://root',
                'app',
                'Task',
                'MyTask.php'
            ]
        );

        $o->execute();
        $expected = <<<E
GENERATION OF NEW CLASS

Enter the task name []: Enter the cron expression [* * * * *]: Enter the properties list (empty value to finish):
Property full class name: Generation of new task class [MyApp\Task\MyTask]

Class: MyApp\Task\MyTask
Path: $classPath
Namespace: MyApp\Task
Are you confirm the generation of [MyApp\Task\MyTask] ?Class [MyApp\Task\MyTask] generated successfully.

E;
        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteWithProperties(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $actionName = 'Task/' . 'MyTask';
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, [
            'getNamespace' => 'MyApp\\',
            'getAppPath' => $dir->url()
        ]);

        $this->createInputContent('mytask');
        $this->createInputContent("\n");
        $this->createInputContent('* * * * *');
        $this->createInputContent("\n");
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

        $o = new MakeTaskCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:task', $o->getName());

        $o->interact($reader, $writer);
        $o->execute();

        $classPath = implode(
            DIRECTORY_SEPARATOR,
            [
                'vfs://root',
                'app',
                'Task',
                'MyTask.php'
            ]
        );

        $expected = <<<E
GENERATION OF NEW CLASS

Enter the task name []: Enter the cron expression [* * * * *]: Enter the properties list (empty value to finish):
Property full class name: Property full class name: Property full class name: Generation of new task class [MyApp\Task\MyTask]

Class: MyApp\Task\MyTask
Path: $classPath
Namespace: MyApp\Task
Are you confirm the generation of [MyApp\Task\MyTask] ?Class [MyApp\Task\MyTask] generated successfully.

E;
        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }


    public function testExecuteWrongExpression(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $actionName = 'Task/' . 'MyTask';
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, [
            'getNamespace' => 'MyApp\\',
            'getAppPath' => $dir->url()
        ]);

        $this->createInputContent('mytask');
        $this->createInputContent("\n");
        $this->createInputContent('* * * * 7');
        $this->createInputContent("\n");
        $this->createInputContent('*/10 * * * *');
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

        $o = new MakeTaskCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:task', $o->getName());

        $o->interact($reader, $writer);
        $o->execute();

        $classPath = implode(
            DIRECTORY_SEPARATOR,
            [
                'vfs://root',
                'app',
                'Task',
                'MyTask.php'
            ]
        );

        $expected = <<<E
GENERATION OF NEW CLASS

Enter the task name []: Enter the cron expression [* * * * *]: Invalid expression, please enter the cron expression [* * * * *]: Enter the properties list (empty value to finish):
Property full class name: Generation of new task class [MyApp\Task\MyTask]

Class: MyApp\Task\MyTask
Path: $classPath
Namespace: MyApp\Task
Are you confirm the generation of [MyApp\Task\MyTask] ?Class [MyApp\Task\MyTask] generated successfully.

E;
        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteWithPropertiesClassNotExist(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $actionName = 'Task/' . 'MyTask';
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, [
            'getNamespace' => 'MyApp\\',
            'getAppPath' => $dir->url()
        ]);

        $this->createInputContent('mytask');
        $this->createInputContent("\n");
        $this->createInputContent('* * * * *');
        $this->createInputContent("\n");
        $this->createInputContent('Platine\Framework\App\Application');
        $this->createInputContent("\n");
        $this->createInputContent('Platine\Template\Template');
        $this->createInputContent("\n");
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

        $o = new MakeTaskCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:task', $o->getName());

        $o->interact($reader, $writer);
        $o->execute();

        $classPath = implode(
            DIRECTORY_SEPARATOR,
            [
                'vfs://root',
                'app',
                'Task',
                'MyTask.php'
            ]
        );

        $expected = <<<E
GENERATION OF NEW CLASS

Enter the task name []: Enter the cron expression [* * * * *]: Enter the properties list (empty value to finish):
Property full class name: Property full class name: Property full class name: The class [Foo\Bar\Not\Found] does not exists
Property full class name: Generation of new task class [MyApp\Task\MyTask]

Class: MyApp\Task\MyTask
Path: $classPath
Namespace: MyApp\Task
Are you confirm the generation of [MyApp\Task\MyTask] ?Class [MyApp\Task\MyTask] generated successfully.

E;
        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }

    public function testGetClassTemplate(): void
    {
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, []);

        $o = new MakeTaskCommand($app, $filesystem);

        $this->assertNotEmpty($o->getClassTemplate());
    }
}
