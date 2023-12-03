<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Task\Command;

use Platine\Config\Config;
use Platine\Console\Application as ConsoleApp;
use Platine\Console\IO\Interactor;
use Platine\Framework\App\Application;
use Platine\Framework\Task\Command\SchedulerRunCommand;
use Platine\Framework\Task\Scheduler;
use Platine\Logger\Logger;
use Platine\Test\Framework\Console\BaseCommandTestCase;
use Platine\Test\Framework\Fixture\MyServiceProvider;
use Platine\Test\Framework\Fixture\MyTask;
use Platine\Test\Framework\Fixture\MyTask2;

/*
 * @group core
 * @group framework
 */
class SchedulerRunCommandTest extends BaseCommandTestCase
{
    public function testExecuteSuccess(): void
    {
        $provider = $this->getMockInstance(MyServiceProvider::class);
        $app = $this->getMockInstance(Application::class, [
            'getProviders' => [$provider],
        ]);

        $writer = $this->getWriterInstance();
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer
        ]);
        $consoleApp = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                [
                    'tasks',
                    [],
                    [new MyTask2()]
                ],
            ]
        ]);

        $logger = $this->getMockInstance(Logger::class);
        $scheduler = new Scheduler($logger);

        $o = new SchedulerRunCommand($scheduler, $app, $config);
        $o->bind($consoleApp);
        $o->parse(['scheduler:run']);

        $this->expectOutputString(sprintf('%s::run', MyTask::class));

        $o->execute();
    }

    public function testRunGivenTask(): void
    {
        global $mock_time_to_1000000000;

        $mock_time_to_1000000000 = true;
        $provider = $this->getMockInstance(MyServiceProvider::class);
        $app = $this->getMockInstance(Application::class, [
            'getProviders' => [$provider],
        ]);

        $writer = $this->getWriterInstance();
        $interactor = $this->getMockInstance(Interactor::class, [
            'writer' => $writer
        ]);
        $consoleApp = $this->getMockInstance(ConsoleApp::class, [
            'io' => $interactor
        ]);
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                [
                    'tasks',
                    [],
                    [new MyTask2()]
                ],
            ]
        ]);

        $logger = $this->getMockInstance(Logger::class);
        $scheduler = new Scheduler($logger);

        $o = new SchedulerRunCommand($scheduler, $app, $config);
        $o->bind($consoleApp);
        $o->parse(['scheduler:run', 'mytask2']);

        $this->expectOutputString(sprintf('%s::run', MyTask::class));

        $o->execute();
    }
}
