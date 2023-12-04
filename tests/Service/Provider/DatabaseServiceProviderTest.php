<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Service\Provider;

use Platine\Database\Configuration;
use Platine\Database\Connection;
use Platine\Database\Exception\ConnectionException;
use Platine\Database\Pool;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Service\Provider\DatabaseServiceProvider;
use Platine\Test\Framework\Fixture\MyApp;

/*
 * @group core
 * @group framework
 */
class DatabaseServiceProviderTest extends PlatineTestCase
{
    public function testRegister(): void
    {
        $app = new MyApp();


        $o = new DatabaseServiceProvider($app);
        $o->register();
        $this->assertInstanceOf(Configuration::class, $app->get(Configuration::class));
        $this->assertInstanceOf(Pool::class, $app->get(Pool::class));
        $this->expectException(ConnectionException::class);
        $this->assertInstanceOf(Connection::class, $app->get(Connection::class));
    }
}
