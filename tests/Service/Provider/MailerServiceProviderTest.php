<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Service\Provider;

use Platine\Config\Config;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Service\Provider\MailerServiceProvider;
use Platine\Mail\Transport\Mail;
use Platine\Mail\Transport\NullTransport;
use Platine\Mail\Transport\TransportInterface;
use Platine\Test\Framework\Fixture\MyApp;
use Platine\Test\Framework\Fixture\MyConfig;
use Platine\Test\Framework\Fixture\MyNullMaillerConfig;

/*
 * @group core
 * @group framework
 */
class MailerServiceProviderTest extends PlatineTestCase
{
    public function testRegister(): void
    {
        $app = new MyApp();

        $app->bind(Config::class, MyConfig::class);

        $o = new MailerServiceProvider($app);
        $o->register();
        $this->assertInstanceOf(Mail::class, $app->get(TransportInterface::class));
    }

    public function testRegisterNullProvider(): void
    {
        $app = new MyApp();

        $app->bind(Config::class, MyNullMaillerConfig::class);

        $o = new MailerServiceProvider($app);
        $o->register();
        $this->assertInstanceOf(NullTransport::class, $app->get(TransportInterface::class));
    }
}
