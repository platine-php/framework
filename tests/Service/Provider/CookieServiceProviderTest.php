<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Service\Provider;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\App\Application;
use Platine\Framework\Service\Provider\CookieServiceProvider;

/*
 * @group core
 * @group framework
 */
class CookieServiceProviderTest extends PlatineTestCase
{
    public function testRegister(): void
    {
        $app = $this->getMockInstanceMap(Application::class);

        $app->expects($this->exactly(1))
                ->method('bind');

        $app->expects($this->exactly(1))
                ->method('share');

        $o = new CookieServiceProvider($app);
        $o->register();
    }
}
