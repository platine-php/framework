<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Kernel;

use InvalidArgumentException;
use Platine\Config\Config;
use Platine\Console\Application as ConsoleApp;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\App\Application;
use Platine\Framework\Kernel\ConsoleKernel;
use Platine\Test\Framework\Fixture\MyCommand;

/*
 * @group core
 * @group framework
 */
class ConsoleKernelTest extends PlatineTestCase
{
    public function testConstruct(): void
    {
        $app = $this->getMockInstance(Application::class);
        $console = $this->getMockInstance(ConsoleApp::class);

        $o = new ConsoleKernel($app, $console);
        $this->assertInstanceOf(ConsoleApp::class, $o->getConsoleApp());
    }

    public function testBootstrap(): void
    {
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['commands', [], [MyCommand::class]],
            ]
        ]);
        $console = $this->getMockInstance(ConsoleApp::class);
        $app = $this->getMockInstanceMap(Application::class, [
            'get' => [
                [Config::class, $config],
            ],
            'getProvidersCommands' => [
                [[MyCommand::class]]
            ]
        ]);

        $o = new ConsoleKernel($app, $console);
        $o->bootstrap();

        $this->assertCount(2, $this->getPropertyValue(ConsoleKernel::class, $o, 'commands'));
    }

    public function testRun(): void
    {
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['commands', [], [MyCommand::class]],
            ]
        ]);
        $app = $this->getMockInstanceMap(Application::class, [
            'get' => [
                [Config::class, $config],
            ],
        ]);
        $console = $this->getMockInstance(ConsoleApp::class);

        $console->expects($this->exactly(1))
                ->method('handle');

        $o = new ConsoleKernel($app, $console);
        $o->run([]);
    }

    public function testCreateCommandUsingContainer(): void
    {
        $app = $this->getMockInstance(Application::class, [
            'has' => true,
            'get' => new MyCommand(),
        ]);
        $console = $this->getMockInstance(ConsoleApp::class);

        $o = new ConsoleKernel($app, $console);

        $res = $this->runPrivateProtectedMethod($o, 'createCommand', [MyCommand::class]);
        $this->assertInstanceOf(MyCommand::class, $res);
    }

    public function testCreateCommandUsingClassInstance(): void
    {
        $app = $this->getMockInstance(Application::class, [
            'has' => false,
        ]);
        $console = $this->getMockInstance(ConsoleApp::class);

        $o = new ConsoleKernel($app, $console);

        $res = $this->runPrivateProtectedMethod($o, 'createCommand', [MyCommand::class]);
        $this->assertInstanceOf(MyCommand::class, $res);
    }

    public function testCreateCommandError(): void
    {
        $app = $this->getMockInstance(Application::class, [
            'has' => false,
        ]);
        $console = $this->getMockInstance(ConsoleApp::class);

        $o = new ConsoleKernel($app, $console);

        $this->expectException(InvalidArgumentException::class);
        $this->runPrivateProtectedMethod($o, 'createCommand', ['foo_command_not_exist']);
    }
}
