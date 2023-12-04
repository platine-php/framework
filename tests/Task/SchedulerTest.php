<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Task;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Task\Scheduler;
use Platine\Framework\Task\TaskInterface;
use Platine\Logger\Logger;
use Platine\Test\Framework\Fixture\MyTask;
use Platine\Test\Framework\Fixture\MyTask2;
use Platine\Test\Framework\Fixture\MyTaskException;
use RuntimeException;

/*
 * @group core
 * @group framework
 */
class SchedulerTest extends PlatineTestCase
{
    public function testConstruct(): void
    {
        $logger = $this->getMockInstance(Logger::class);
        $o = new Scheduler($logger);

        $this->assertInstanceOf(Scheduler::class, $o);
    }

    public function testAddDuplicate(): void
    {
        $logger = $this->getMockInstance(Logger::class);
        $o = new Scheduler($logger);

        $o->add(new MyTask());

        $this->assertCount(1, $o->all());

        $this->expectException(RuntimeException::class);
        $o->add(new MyTask());
    }

    public function testAdd(): void
    {
        $logger = $this->getMockInstance(Logger::class);
        $o = new Scheduler($logger);

        $o->add(new MyTask());

        $this->assertCount(1, $o->all());
        $this->assertInstanceOf(TaskInterface::class, $o->get('mytask'));
        $this->assertNull($o->get('not_found_task'));
    }

    public function testRemove(): void
    {
        $logger = $this->getMockInstance(Logger::class);
        $o = new Scheduler($logger);

        $o->add(new MyTask());
        $o->add(new MyTaskException());

        $this->assertCount(2, $o->all());
        $this->assertInstanceOf(TaskInterface::class, $o->get('mytask'));
        $this->assertInstanceOf(TaskInterface::class, $o->get('mytask_exception'));
        $this->assertNull($o->get('not_found_task'));

        $o->remove('mytask_exception');
        $this->assertCount(1, $o->all());
        $this->assertInstanceOf(TaskInterface::class, $o->get('mytask'));
        $this->assertNull($o->get('mytask_exception'));
        $this->assertNull($o->get('not_found_task'));

        $o->removeAll();
        $this->assertCount(0, $o->all());
        $this->assertNull($o->get('mytask'));
        $this->assertNull($o->get('mytask_exception'));
        $this->assertNull($o->get('not_found_task'));
    }

    public function testExecute(): void
    {
        $logger = $this->getMockInstance(Logger::class);
        $o = new Scheduler($logger);

        $this->expectOutputString(sprintf('%s::run', MyTask::class));
        $o->execute(new MyTask());
    }

    public function testExecuteException(): void
    {
        $logger = $this->getMockInstance(Logger::class);

        $logger->expects($this->exactly(1))
                ->method('error');

        $o = new Scheduler($logger);

        $o->execute(new MyTaskException());
    }

    public function testRun(): void
    {
        $logger = $this->getMockInstance(Logger::class);
        $o = new Scheduler($logger);

        $o->add(new MyTask2());
        $o->add(new MyTaskException());

        $this->expectOutputString(sprintf('%s::run', MyTask::class));
        $o->run();
    }
}
