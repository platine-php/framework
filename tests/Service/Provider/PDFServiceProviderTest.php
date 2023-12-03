<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Service\Provider;

use Dompdf\Dompdf;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Service\Provider\PDFServiceProvider;
use Platine\Test\Framework\Fixture\MyApp;

/*
 * @group core
 * @group framework
 */
class PDFServiceProviderTest extends PlatineTestCase
{
    public function testRegister(): void
    {
        $app = new MyApp();


        $o = new PDFServiceProvider($app);
        $o->register();
        $this->assertInstanceOf(Dompdf::class, $app->get(Dompdf::class));
    }
}
