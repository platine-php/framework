<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Service\Provider;

use Platine\Config\Config;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Service\Provider\MailerSMTPServiceProvider;
use Platine\Mail\Transport\SMTP;
use Platine\Mail\Transport\TransportInterface;
use Platine\Test\Framework\Fixture\MyApp;
use Platine\Test\Framework\Fixture\MyConfig;

/*
 * @group core
 * @group framework
 */
class MailerSMTPServiceProviderTest extends PlatineTestCase
{
    public function testRegister(): void
    {
        $app = new MyApp();

        $app->bind(Config::class, MyConfig::class);

        $o = new MailerSMTPServiceProvider($app);
        $o->register();
        $this->assertInstanceOf(SMTP::class, $app->get(TransportInterface::class));
    }
}
