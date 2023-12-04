<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Service\Provider;

use Platine\Console\Application;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Http\Emitter\EmitterInterface;
use Platine\Framework\Service\Provider\BaseServiceProvider;
use Platine\Test\Framework\Fixture\MyApp;

/*
 * @group core
 * @group framework
 */
class BaseServiceProviderTest extends PlatineTestCase
{
    public function testRegister(): void
    {
        $app = new MyApp();


        $o = new BaseServiceProvider($app);
        $o->register();
        $this->assertInstanceOf(Application::class, $app->get(Application::class));
        $this->assertInstanceOf(EmitterInterface::class, $app->get(EmitterInterface::class));
    }
}
