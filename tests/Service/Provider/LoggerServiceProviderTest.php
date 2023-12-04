<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Service\Provider;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Service\Provider\LoggerServiceProvider;
use Platine\Logger\LoggerInterface;
use Platine\Test\Framework\Fixture\MyApp;

/*
 * @group core
 * @group framework
 */
class LoggerServiceProviderTest extends PlatineTestCase
{
    public function testRegister(): void
    {
        $app = new MyApp();


        $o = new LoggerServiceProvider($app);
        $o->register();
        $this->assertInstanceOf(LoggerInterface::class, $app->get(LoggerInterface::class));
    }
}
