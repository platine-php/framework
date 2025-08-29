<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Service\Provider;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\App\Application;
use Platine\Framework\Security\SecurityPolicy;
use Platine\Framework\Service\Provider\SecurityServiceProvider;
use Platine\Test\Framework\Fixture\MyApp;

/*
 * @group core
 * @group framework
 */
class SecurityServiceProviderTest extends PlatineTestCase
{
    public function testRegister(): void
    {
        $app = $this->getMockInstanceMap(Application::class);

        $app->expects($this->exactly(7))
                ->method('bind');

        $o = new SecurityServiceProvider($app);
        $o->register();
    }

    public function testMockAppRegister(): void
    {
        $app = new MyApp();


        $o = new SecurityServiceProvider($app);
        $o->register();
        $this->assertInstanceOf(SecurityPolicy::class, $app->get(SecurityPolicy::class));
    }
}
