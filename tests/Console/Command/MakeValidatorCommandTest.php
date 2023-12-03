<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Console\Command;

use Platine\Console\Application as ConsoleApp;
use Platine\Console\IO\Interactor;
use Platine\Filesystem\Adapter\Local\LocalAdapter;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Console\Command\MakeValidatorCommand;
use Platine\Test\Framework\Console\BaseCommandTestCase;

/*
 * @group core
 * @group framework
 */
class MakeValidatorCommandTest extends BaseCommandTestCase
{
    public function testExecuteDefault(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $actionName = 'Validator/' . 'MyValidator';
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

        $o = new MakeValidatorCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:validator', $o->getName());

        $o->interact($reader, $writer);
        $o->execute();

        $classPath = implode(
            DIRECTORY_SEPARATOR,
            [
                'vfs://root',
                'app',
                'Validator',
                'MyValidator.php'
            ]
        );

        $expected = <<<E
GENERATION OF NEW CLASS

Enter the form parameter full class name: Generation of new validator class [MyApp\Validator\MyValidator]

Class: MyApp\Validator\MyValidator
Path: $classPath
Namespace: MyApp\Validator
Class [MyApp\Validator\MyValidator] generated successfully.

E;
        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteParameterClassNotExists(): void
    {
        $this->createInputContent('Foo\Bar\Not\Found');
        $this->createInputContent("\n");
        $this->createInputContent('Platine\Framework\App\Application');

        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $actionName = 'Validator/' . 'MyValidator';
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

        $o = new MakeValidatorCommand($app, $filesystem);

        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:validator', $o->getName());

        $o->interact($reader, $writer);

        $o->execute();

        $classPath = implode(
            DIRECTORY_SEPARATOR,
            [
                'vfs://root',
                'app',
                'Validator',
                'MyValidator.php'
            ]
        );

        $expected = <<<E
GENERATION OF NEW CLASS

Enter the form parameter full class name: Class does not exists, please enter the form parameter full class name: Generation of new validator class [MyApp\Validator\MyValidator]

Class: MyApp\Validator\MyValidator
Path: $classPath
Namespace: MyApp\Validator
Class [MyApp\Validator\MyValidator] generated successfully.

E;
        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }


    public function testGetClassTemplate(): void
    {
        $this->createInputContent('Platine\Framework\App\Application');

        $actionName = 'Validator/' . 'MyValidator';

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

        $o = new MakeValidatorCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:validator', $o->getName());

        $o->interact($reader, $writer);

        $this->assertNotEmpty($o->getClassTemplate());
    }

    public function testGetUsesContent(): void
    {
        $this->createInputContent('Platine\Framework\App\Application');

        $actionName = 'Validator/' . 'MyValidator';

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

        $o = new MakeValidatorCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:validator', $o->getName());

        $o->interact($reader, $writer);

        $result = $this->runPrivateProtectedMethod($o, 'getUsesContent');

        $this->assertNotEmpty($result);
    }
}
