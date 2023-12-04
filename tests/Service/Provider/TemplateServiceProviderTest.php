<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Service\Provider;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Service\Provider\TemplateServiceProvider;
use Platine\Template\Configuration;
use Platine\Test\Framework\Fixture\MyApp;

/*
 * @group core
 * @group framework
 */
class TemplateServiceProviderTest extends PlatineTestCase
{
    public function testRegister(): void
    {
        $app = new MyApp();

        $o = new TemplateServiceProvider($app);
        $o->register();
        $this->assertInstanceOf(Configuration::class, $app->get(Configuration::class));
    }
}
