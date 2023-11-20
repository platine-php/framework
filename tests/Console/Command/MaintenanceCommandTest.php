<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Console\Command;

use Platine\Config\Config;
use Platine\Console\Application as ConsoleApp;
use Platine\Console\IO\Interactor;
use Platine\Framework\App\Application;
use Platine\Framework\Console\Command\MaintenanceCommand;
use Platine\Test\Framework\Console\BaseCommandTestCase;
use RuntimeException;

/*
 * @group core
 * @group framework
 */
class MaintenanceCommandTest extends BaseCommandTestCase
{
    public function testExecuteDefault(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $app = $this->getMockInstance(Application::class, [
            'getAppPath' => $dir->url()
        ]);

        $mockInfo = $this->getConsoleApp();

        $o = new MaintenanceCommand($app, $mockInfo[3]);
        $o->bind($mockInfo[0]);
        $o->parse(['platine', 'status']);
        $this->assertEquals('maintenance', $o->getName());

        $o->interact($mockInfo[1], $mockInfo[2]);
        $o->execute();

        $expected = 'APPLICATION MAINTENANCE MANAGEMENT

Application is online.
';

        $this->assertEquals($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteWrongArgument(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $app = $this->getMockInstance(Application::class, [
            'getAppPath' => $dir->url()
        ]);

        $mockInfo = $this->getConsoleApp();

        $o = new MaintenanceCommand($app, $mockInfo[3]);
        $o->bind($mockInfo[0]);

        $this->expectExceptionMessage(RuntimeException::class);
        $this->expectExceptionMessage('Invalid argument type [not-found], must be one of [up, down, status]');

        $o->parse(['platine', 'not-found']);
        $this->assertEquals('maintenance', $o->getName());

        $o->interact($mockInfo[1], $mockInfo[2]);
        $o->execute();
    }

    public function testExecuteDisableMaintenanceAlreadyOnline(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $app = $this->getMockInstance(Application::class, [
            'getAppPath' => $dir->url()
        ]);

        $mockInfo = $this->getConsoleApp();

        $o = new MaintenanceCommand($app, $mockInfo[3]);
        $o->bind($mockInfo[0]);

        $o->parse(['platine', 'up']);
        $this->assertEquals('maintenance', $o->getName());

        $o->interact($mockInfo[1], $mockInfo[2]);
        $o->execute();

        $expected = 'APPLICATION MAINTENANCE MANAGEMENT

Application already online
';
        $this->assertEquals($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteWrongRetryValue(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $app = $this->getMockInstance(Application::class, [
            'getAppPath' => $dir->url()
        ]);


        $mockInfo = $this->getConsoleApp();

        $o = new MaintenanceCommand($app, $mockInfo[3]);
        $o->bind($mockInfo[0]);

        $o->parse(['platine', 'down', '-r=-10']);
        $this->assertEquals('maintenance', $o->getName());

        $o->interact($mockInfo[1], $mockInfo[2]);
        $o->execute();

        $expected = '';

        $this->assertEquals($expected, $this->getConsoleOutputContent());
    }
}
