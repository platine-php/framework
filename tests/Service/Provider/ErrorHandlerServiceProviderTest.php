<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Service\Provider;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Handler\Error\ErrorHandlerInterface;
use Platine\Framework\Http\Middleware\ErrorHandlerMiddleware;
use Platine\Framework\Service\Provider\ErrorHandlerServiceProvider;
use Platine\Test\Framework\Fixture\MyApp;

/*
 * @group core
 * @group framework
 */
class ErrorHandlerServiceProviderTest extends PlatineTestCase
{
    public function testRegister(): void
    {
        $app = new MyApp();


        $o = new ErrorHandlerServiceProvider($app);
        $o->register();
        $this->assertInstanceOf(ErrorHandlerInterface::class, $app->get(ErrorHandlerInterface::class));
        $this->assertInstanceOf(ErrorHandlerMiddleware::class, $app->get(ErrorHandlerMiddleware::class));
    }
}
