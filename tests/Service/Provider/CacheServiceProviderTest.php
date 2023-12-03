<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Service\Provider;

use Platine\Cache\Configuration;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Http\Emitter\EmitterInterface;
use Platine\Framework\Service\Provider\CacheServiceProvider;
use Platine\Test\Framework\Fixture\MyApp;

/*
 * @group core
 * @group framework
 */
class CacheServiceProviderTest extends PlatineTestCase
{
    public function testRegister(): void
    {
        $app = new MyApp();


        $o = new CacheServiceProvider($app);
        $o->register();
        $this->assertInstanceOf(Configuration::class, $app->get(Configuration::class));
        $this->assertInstanceOf(EmitterInterface::class, $app->get(EmitterInterface::class));
    }
}
