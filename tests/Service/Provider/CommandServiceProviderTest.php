<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Service\Provider;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\App\Application;
use Platine\Framework\Service\Provider\CommandServiceProvider;

/*
 * @group core
 * @group framework
 */
class CommandServiceProviderTest extends PlatineTestCase
{
    public function testRegister(): void
    {
        $app = $this->getMockInstanceMap(Application::class);

        $app->expects($this->exactly(17))
                ->method('bind');

        $o = new CommandServiceProvider($app);
        $o->register();
    }
}
