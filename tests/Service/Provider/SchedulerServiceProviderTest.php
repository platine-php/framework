<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Service\Provider;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\App\Application;
use Platine\Framework\Service\Provider\SchedulerServiceProvider;

/*
 * @group core
 * @group framework
 */
class SchedulerServiceProviderTest extends PlatineTestCase
{
    public function testRegister(): void
    {
        $app = $this->getMockInstanceMap(Application::class);

        $app->expects($this->exactly(3))
                ->method('bind');

        $o = new SchedulerServiceProvider($app);
        $o->register();
    }
}
