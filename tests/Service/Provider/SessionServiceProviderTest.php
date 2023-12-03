<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Service\Provider;

use Platine\Dev\PlatineTestCase;
use Platine\Filesystem\Adapter\AdapterInterface;
use Platine\Filesystem\Adapter\Local\LocalAdapter;
use Platine\Filesystem\Filesystem;
use Platine\Framework\Service\Provider\SessionServiceProvider;
use Platine\Session\Configuration;
use Platine\Session\Exception\FileSessionHandlerException;
use Platine\Test\Framework\Fixture\MyApp;
use SessionHandlerInterface;

/*
 * @group core
 * @group framework
 */
class SessionServiceProviderTest extends PlatineTestCase
{
    public function testRegister(): void
    {
        $adapter = $this->getMockInstance(LocalAdapter::class);

        $app = new MyApp();

        $app->instance($adapter, AdapterInterface::class);

        $o = new SessionServiceProvider($app);
        $o->register();

        $app->bind(Filesystem::class);

        $this->assertInstanceOf(Configuration::class, $app->get(Configuration::class));
        $this->expectException(FileSessionHandlerException::class);
        $this->assertInstanceOf(SessionHandlerInterface::class, $app->get(SessionHandlerInterface::class));
    }
}
