<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Console\Command;

use Platine\Framework\App\Application;
use Platine\Framework\Console\Command\MaintenanceCommand;
use Platine\Test\Framework\Console\BaseCommandTestCase;
use RuntimeException;

use function Platine\Test\Framework\Fixture\getTestMaintenanceDriver;

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

        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
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

        $this->expectException(RuntimeException::class);
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
        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
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

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid retry value [0], must be an integer greather than zero');

        $o->parse(['platine', 'down', '-r=0']);
        $this->assertEquals('maintenance', $o->getName());

        $o->interact($mockInfo[1], $mockInfo[2]);
        $o->execute();
    }

    public function testExecuteWrongRefreshValue(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $app = $this->getMockInstance(Application::class, [
            'getAppPath' => $dir->url()
        ]);


        $mockInfo = $this->getConsoleApp();

        $o = new MaintenanceCommand($app, $mockInfo[3]);
        $o->bind($mockInfo[0]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid refresh value [0], must be an integer greather than zero');

        $o->parse(['platine', 'down', '-e=0']);
        $this->assertEquals('maintenance', $o->getName());

        $o->interact($mockInfo[1], $mockInfo[2]);
        $o->execute();
    }

    public function testExecuteWrongHttpStatusValue(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $app = $this->getMockInstance(Application::class, [
            'getAppPath' => $dir->url()
        ]);


        $mockInfo = $this->getConsoleApp();

        $o = new MaintenanceCommand($app, $mockInfo[3]);
        $o->bind($mockInfo[0]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid HTTP status value [100], must be between 200 and 505');

        $o->parse(['platine', 'down', '-c=100']);
        $this->assertEquals('maintenance', $o->getName());

        $o->interact($mockInfo[1], $mockInfo[2]);
        $o->execute();
    }

    public function testExecuteStatusDown(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $app = $this->getMockInstance(Application::class, [
            'getAppPath' => $dir->url(),
            'isInMaintenance' => true,
        ]);

        $mockInfo = $this->getConsoleApp();

        $o = new MaintenanceCommand($app, $mockInfo[3]);
        $o->bind($mockInfo[0]);

        $o->parse(['platine', 'status']);
        $this->assertEquals('maintenance', $o->getName());

        $o->interact($mockInfo[1], $mockInfo[2]);
        $o->execute();

        $expected = 'APPLICATION MAINTENANCE MANAGEMENT

Application is down.
';
        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteDownAlreadyInMaintenance(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $app = $this->getMockInstance(Application::class, [
            'getAppPath' => $dir->url(),
            'isInMaintenance' => true,
        ]);

        $mockInfo = $this->getConsoleApp();

        $o = new MaintenanceCommand($app, $mockInfo[3]);
        $o->bind($mockInfo[0]);

        $o->parse(['platine', 'down']);
        $this->assertEquals('maintenance', $o->getName());

        $o->interact($mockInfo[1], $mockInfo[2]);
        $o->execute();

        $expected = 'APPLICATION MAINTENANCE MANAGEMENT

Application is already down.
';
        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteDownSuccess(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $app = $this->getMockInstance(Application::class, [
            'getAppPath' => $dir->url(),
            'isInMaintenance' => false,
        ]);

        $mockInfo = $this->getConsoleApp();

        $o = new MaintenanceCommand($app, $mockInfo[3]);
        $o->bind($mockInfo[0]);

        $o->parse(['platine', 'down', '-r=100', '-e=1000', '-c=300', '-m="Hello World"']);
        $this->assertEquals('maintenance', $o->getName());

        $o->interact($mockInfo[1], $mockInfo[2]);
        $o->execute();

        $expected = 'APPLICATION MAINTENANCE MANAGEMENT

Application is now in maintenance mode.
';
        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteOnlineSuccess(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $app = $this->getMockInstance(Application::class, [
            'getAppPath' => $dir->url(),
            'isInMaintenance' => true,
        ]);

        $mockInfo = $this->getConsoleApp();

        $o = new MaintenanceCommand($app, $mockInfo[3]);
        $o->bind($mockInfo[0]);

        $o->parse(['platine', 'up']);
        $this->assertEquals('maintenance', $o->getName());

        $o->interact($mockInfo[1], $mockInfo[2]);
        $o->execute();

        $expected = 'APPLICATION MAINTENANCE MANAGEMENT

Application is now online
';
        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteOnlineError(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $app = $this->getMockInstance(Application::class, [
            'getAppPath' => $dir->url(),
            'isInMaintenance' => true,
            'maintenance' => getTestMaintenanceDriver(true, true),
        ]);

        $mockInfo = $this->getConsoleApp();

        $o = new MaintenanceCommand($app, $mockInfo[3]);
        $o->bind($mockInfo[0]);

        $o->parse(['platine', 'up']);
        $this->assertEquals('maintenance', $o->getName());

        $o->interact($mockInfo[1], $mockInfo[2]);
        $o->execute();

        $expected = 'APPLICATION MAINTENANCE MANAGEMENT

Failed to disable maintenance mode: Maintenance deactivate error.
';
        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteDownError(): void
    {
        $dir = $this->createVfsDirectory('app', $this->vfsRoot);
        $app = $this->getMockInstance(Application::class, [
            'getAppPath' => $dir->url(),
            'isInMaintenance' => false,
            'maintenance' => getTestMaintenanceDriver(true, false),
        ]);

        $mockInfo = $this->getConsoleApp();

        $o = new MaintenanceCommand($app, $mockInfo[3]);
        $o->bind($mockInfo[0]);

        $o->parse(['platine', 'down']);
        $this->assertEquals('maintenance', $o->getName());

        $o->interact($mockInfo[1], $mockInfo[2]);
        $o->execute();

        $expected = 'APPLICATION MAINTENANCE MANAGEMENT

Failed to enable maintenance mode: Maintenance activate error.
';
        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }
}
