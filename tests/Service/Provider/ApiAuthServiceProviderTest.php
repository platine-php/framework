<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Service\Provider;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\App\Application;
use Platine\Framework\Service\Provider\ApiAuthServiceProvider;

/*
 * @group core
 * @group framework
 */
class ApiAuthServiceProviderTest extends PlatineTestCase
{
    public function testRegister(): void
    {
        $app = $this->getMockInstanceMap(Application::class);

        $app->expects($this->exactly(6))
                ->method('bind');
        $o = new ApiAuthServiceProvider($app);
        $o->register();
    }
}
