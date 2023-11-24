<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Service\Provider;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Service\Provider\EncryptionServiceProvider;
use Platine\Security\Encryption;
use Platine\Test\Framework\Fixture\MyApp;

/*
 * @group core
 * @group framework
 */
class EncryptionServiceProviderTest extends PlatineTestCase
{
    public function testRegister(): void
    {
        $app = new MyApp();


        $o = new EncryptionServiceProvider($app);
        $o->register();
        $this->assertInstanceOf(Encryption::class, $app->get(Encryption::class));
    }
}
