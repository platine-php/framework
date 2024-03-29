<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Service\Provider;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\App\Application;
use Platine\Framework\Service\Provider\AuthServiceProvider;

/*
 * @group core
 * @group framework
 */
class AuthServiceProviderTest extends PlatineTestCase
{
    public function testRegister(): void
    {
        $app = $this->getMockInstanceMap(Application::class);

        $app->expects($this->exactly(9))
                ->method('bind');
        $o = new AuthServiceProvider($app);
        $o->register();
    }
}
