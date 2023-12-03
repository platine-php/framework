<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Service\Provider;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Service\Provider\PaginationServiceProvider;
use Platine\Http\ServerRequest;
use Platine\Http\ServerRequestInterface;
use Platine\Pagination\Pagination;
use Platine\Pagination\UrlGeneratorInterface;
use Platine\Test\Framework\Fixture\MyApp;

/*
 * @group core
 * @group framework
 */
class PaginationServiceProviderTest extends PlatineTestCase
{
    public function testRegister(): void
    {
        $app = new MyApp();
        $app->bind(ServerRequestInterface::class, ServerRequest::class);

        $o = new PaginationServiceProvider($app);
        $o->register();
        $this->assertInstanceOf(UrlGeneratorInterface::class, $app->get(UrlGeneratorInterface::class));
        $this->assertInstanceOf(Pagination::class, $app->get(Pagination::class));
    }
}
