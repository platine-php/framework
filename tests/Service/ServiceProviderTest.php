<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Service;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\App\Application;
use Platine\Framework\Service\ServiceProvider;
use Platine\Route\Router;
use Platine\Test\Framework\Fixture\MyCommand;
use Platine\Test\Framework\Fixture\MyEventListener;
use Platine\Test\Framework\Fixture\MyEventSubscriber;
use Platine\Test\Framework\Fixture\MyTask;

/*
 * @group core
 * @group framework
 */
class ServiceProviderTest extends PlatineTestCase
{
    public function testDefault(): void
    {
        $app = $this->getMockInstanceMap(Application::class);
        $router = $this->getMockInstanceMap(Router::class);

        $app->expects($this->exactly(1))
                ->method('listen');

        $app->expects($this->exactly(1))
                ->method('subscribe');

        $o = new ServiceProvider($app);

        $o->boot(); // fake

        $o->register();
        $o->addRoutes($router);
        $o->listen('event', new MyEventListener());
        $o->subscribe(new MyEventSubscriber());
        $o->addTask(MyTask::class);
        $o->addCommand(MyCommand::class);

        $this->assertCount(1, $o->getTasks());
        $this->assertCount(1, $o->getCommands());
    }
}
