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

        $app->expects($this->exactly(4))
                ->method('bind');

        $app->expects($this->exactly(5))
                ->method('share');
        $o = new AuthServiceProvider($app);
        $o->register();
    }
}
