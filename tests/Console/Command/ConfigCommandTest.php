<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Console\Command;

use Platine\Config\Config;
use Platine\Console\Application;
use Platine\Console\IO\Interactor;
use Platine\Framework\Console\Command\ConfigCommand;
use Platine\Test\Framework\Console\BaseCommandTestCase;

/*
 * @group core
 * @group framework
 */
class ConfigCommandTest extends BaseCommandTestCase
{
    public function testExecuteDefault(): void
    {
        $writer = $this->getWriterInstance();
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer
        ]);
        $app = $this->getMockInstance(Application::class, [
            'io' => $interactor
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['app', [], [
                    'name' => 'Foo',
                    'debug' => false
                ]],
            ]
        ]);

        $o = new ConfigCommand($config);
        $o->bind($app);
        $o->parse(['platine', 'config', '-l']);
        $this->assertEquals('config', $o->getName());
        $o->execute();
        $expected = 'Show configuration for [app]

+-------+-------+
| Name  | Value |
+-------+-------+
| name  | Foo   |
| debug | false |
+-------+-------+

Command finished successfully
';
        $this->assertEquals($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteArrayIndex(): void
    {
        $writer = $this->getWriterInstance();
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer
        ]);
        $app = $this->getMockInstance(Application::class, [
            'io' => $interactor
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['commands', [], [
                    'foo',
                    'bar',
                ]]
            ]
        ]);

        $o = new ConfigCommand($config);
        $o->bind($app);
        $o->parse(['platine', 'config', '-l', '-t', 'commands']);
        $o->execute();
        $expected = 'Show configuration for [commands]

+-------+
| Value |
+-------+
| foo   |
| bar   |
+-------+

Command finished successfully
';
        $this->assertEquals($expected, $this->getConsoleOutputContent());
    }
}
