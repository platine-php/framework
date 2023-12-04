<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Console\Command;

use Platine\Console\Application as ConsoleApp;
use Platine\Console\IO\Interactor;
use Platine\Filesystem\Adapter\Local\LocalAdapter;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Console\Command\MakeMiddlewareCommand;
use Platine\Test\Framework\Console\BaseCommandTestCase;

/*
 * @group core
 * @group framework
 */
class MakeMiddlewareCommandTest extends BaseCommandTestCase
{
    public function testExecuteDefault(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $actionName = 'Middleware/' . 'MyMiddleware';
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

        $o = new MakeMiddlewareCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:middleware', $o->getName());

        $o->interact($reader, $writer);
        $o->execute();

        $classPath = implode(
            DIRECTORY_SEPARATOR,
            [
                'vfs://root',
                'app',
                'Middleware',
                'MyMiddleware.php'
            ]
        );

        $expected = <<<E
GENERATION OF NEW CLASS

Generation of new middleware class [MyApp\Middleware\MyMiddleware]

Class: MyApp\Middleware\MyMiddleware
Path: $classPath
Namespace: MyApp\Middleware

E;
        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }


    public function testGetClassTemplate(): void
    {
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, []);

        $o = new MakeMiddlewareCommand($app, $filesystem);

        $this->assertNotEmpty($o->getClassTemplate());
    }

    public function testGetUsesContent(): void
    {
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, []);

        $o = new MakeMiddlewareCommand($app, $filesystem);

        $result = $this->runPrivateProtectedMethod($o, 'getUsesContent');

        $this->assertEmpty($result);
    }
}
