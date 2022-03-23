<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Service\Provider;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\App\Application;
use Platine\Framework\Service\Provider\AuditServiceProvider;

/*
 * @group core
 * @group framework
 */
class AuditServiceProviderTest extends PlatineTestCase
{
    public function testRegister(): void
    {
        $app = $this->getMockInstanceMap(Application::class);

        $app->expects($this->exactly(2))
                ->method('bind');
        $o = new AuditServiceProvider($app);
        $o->register();
    }
}
