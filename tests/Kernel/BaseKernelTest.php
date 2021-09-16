<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Kernel;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\App\Application;
use Platine\Framework\Kernel\BaseKernel;

/*
 * @group core
 * @group framework
 */
class BaseKernelTest extends PlatineTestCase
{

    public function testBootstrap(): void
    {
        $app = $this->getMockInstance(Application::class);

        $app->expects($this->exactly(1))
                ->method('registerConfiguration');

        $app->expects($this->exactly(1))
                ->method('registerConfiguredServiceProviders');

        $app->expects($this->exactly(1))
                ->method('registerConfiguredEvents');

        $app->expects($this->exactly(1))
                ->method('boot');

        $o = new BaseKernel($app);

        $o->bootstrap();
    }
}
