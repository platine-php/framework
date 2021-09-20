<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Service\Provider;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\App\Application;
use Platine\Framework\Service\Provider\MigrationServiceProvider;

/*
 * @group core
 * @group framework
 */
class MigrationServiceProviderTest extends PlatineTestCase
{
    public function testRegister(): void
    {
        $app = $this->getMockInstanceMap(Application::class);

        $app->expects($this->exactly(11))
                ->method('bind');

        $o = new MigrationServiceProvider($app);
        $o->register();
    }
}
