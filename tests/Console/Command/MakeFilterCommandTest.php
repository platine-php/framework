<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Console\Command;

use Platine\Console\Application as ConsoleApp;
use Platine\Console\IO\Interactor;
use Platine\Filesystem\Adapter\Local\LocalAdapter;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Console\Command\MakeFilterCommand;
use Platine\Framework\Helper\ViewContext;
use Platine\Lang\Lang;
use Platine\Logger\Logger;
use Platine\Test\Framework\Console\BaseCommandTestCase;

/*
 * @group core
 * @group framework
 */
class MakeFilterCommandTest extends BaseCommandTestCase
{
    public function testExecuteUsingLang(): void
    {
        $this->executeDefault(true);
    }

    public function testExecuteWithoutLang(): void
    {
        $this->executeDefault(false);
    }


    protected function executeDefault(bool $useLang): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $actionName = 'Filter/' . 'MyFilter';
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, [
            'getNamespace' => 'MyApp\\',
            'getAppPath' => $dir->url()
        ]);

        if ($useLang) {
            $this->createInputContent(Lang::class);
            $this->createInputContent("\n");
        }
        $this->createInputContent(Logger::class);
        $this->createInputContent("\n");
        $this->createInputContent(ViewContext::class);
        $this->createInputContent("\n");
        $this->createInputContent('');

        $reader = $this->getReaderInstance();
        $writer = $this->getWriterInstance();

        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer,
            'reader' => $reader,
        ], [
             'prompt',
            'confirm',
            'choice',
            'isValidChoice',
        ]);

        $this->setPropertyValue(Interactor::class, $interactor, 'reader', $reader);
        $this->setPropertyValue(Interactor::class, $interactor, 'writer', $writer);

        $consoleApp = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);

        $o = new MakeFilterCommand($app, $filesystem);
        $o->bind($consoleApp);
        $o->parse(['platine', $actionName]);
        $this->assertEquals('make:filter', $o->getName());

        $o->interact($reader, $writer);
        $o->execute();
        $classPath = implode(
            DIRECTORY_SEPARATOR,
            [
                'vfs://root',
                'app',
                'Filter',
                'MyFilter.php'
            ]
        );
        $row = 'Property full class name: Property full class name: Property full '
            . 'class name: Property full class name: '
            . 'Generation of new filter class [MyApp\Filter\MyFilter]';
        if ($useLang === false) {
            $row = 'Property full class name: Property full class name: '
                . 'Property full class name: Generation of new filter '
                . 'class [MyApp\Filter\MyFilter]';
        }
        $expected = <<<E
GENERATION OF NEW CLASS

Enter the properties list (empty value to finish):
$row

Class: MyApp\Filter\MyFilter
Path: $classPath
Namespace: MyApp\Filter
Are you confirm the generation of [MyApp\Filter\MyFilter] ?Class [MyApp\Filter\MyFilter] generated successfully.

E;
        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }



    public function testGetClassTemplate(): void
    {
        $localAdapter = new LocalAdapter();
        $filesystem = new Filesystem($localAdapter);
        $app = $this->getMockInstance(Application::class, []);


        $o = new MakeFilterCommand($app, $filesystem);

        $this->assertNotEmpty($o->getClassTemplate());
    }
}
