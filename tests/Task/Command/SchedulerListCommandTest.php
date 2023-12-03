<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Task\Command;

use InvalidArgumentException;
use Platine\Config\Config;
use Platine\Console\Application as ConsoleApp;
use Platine\Console\IO\Interactor;
use Platine\Framework\App\Application;
use Platine\Framework\Task\Command\SchedulerListCommand;
use Platine\Framework\Task\Scheduler;
use Platine\Logger\Logger;
use Platine\Test\Framework\Console\BaseCommandTestCase;
use Platine\Test\Framework\Fixture\MyServiceProvider;
use Platine\Test\Framework\Fixture\MyTask;
use Platine\Test\Framework\Fixture\MyTaskException;

/*
 * @group core
 * @group framework
 */
class SchedulerListCommandTest extends BaseCommandTestCase
{
    public function testExecuteUsingTaskInContainer(): void
    {
        global $mock_time_to_1000000000;

        $mock_time_to_1000000000 = true;

        $app = $this->getMockInstance(Application::class, [
            'has' => true,
            'get' => new MyTask(),
            'getProvidersTasks' => [MyTask::class],
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
                    []
                ],
            ]
        ]);

        $logger = $this->getMockInstance(Logger::class);
        $scheduler = new Scheduler($logger);

        $o = new SchedulerListCommand($scheduler, $app, $config);
        $o->bind($consoleApp);
        $o->parse(['platine', 'scheduler:list']);
        $o->execute();
        $expected = 'TASK LIST

+--------+--------------+------------------+---------------------------------------+
| Name   | Expression   | Next Execution   | Class                                 |
+--------+--------------+------------------+---------------------------------------+
| mytask | */50 * * * * | 2001-09-09 01:50 | Platine\Test\Framework\Fixture\MyTask |
+--------+--------------+------------------+---------------------------------------+

Command finished successfully
';
        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }

    public function testExecuteClassStringDoesNotExist(): void
    {
        global $mock_time_to_1000000000;

        $mock_time_to_1000000000 = true;

        $app = $this->getMockInstance(Application::class, [
            'has' => false,
            'get' => new MyTask(),
            'getProvidersTasks' => ['foo_task_class_that_does_not_exists'],
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
                    []
                ],
            ]
        ]);

        $logger = $this->getMockInstance(Logger::class);
        $scheduler = new Scheduler($logger);

        $o = new SchedulerListCommand($scheduler, $app, $config);
        $o->bind($consoleApp);
        $o->parse(['platine', 'scheduler:list']);
        $this->expectException(InvalidArgumentException::class);

        $o->execute();
    }

    public function testExecuteSuccess(): void
    {
        global $mock_time_to_1000000000;

        $mock_time_to_1000000000 = true;
        $provider = $this->getMockInstance(MyServiceProvider::class);
        $app = $this->getMockInstance(Application::class, [
            'getProviders' => [$provider],
            'getProvidersTasks' => [MyTask::class],
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
                    [new MyTaskException()]
                ],
            ]
        ]);

        $logger = $this->getMockInstance(Logger::class);
        $scheduler = new Scheduler($logger);

        $o = new SchedulerListCommand($scheduler, $app, $config);
        $o->bind($consoleApp);
        $o->parse(['platine', 'scheduler:list']);
        $o->execute();
        $expected = 'TASK LIST

+------------------+--------------+------------------+------------------------------------------------+
| Name             | Expression   | Next Execution   | Class                                          |
+------------------+--------------+------------------+------------------------------------------------+
| mytask           | */50 * * * * | 2001-09-09 01:50 | Platine\Test\Framework\Fixture\MyTask          |
| mytask_exception | * * * * *    | 2001-09-09 01:46 | Platine\Test\Framework\Fixture\MyTaskException |
+------------------+--------------+------------------+------------------------------------------------+

Command finished successfully
';
        $this->assertCommandOutput($expected, $this->getConsoleOutputContent());
    }
}
