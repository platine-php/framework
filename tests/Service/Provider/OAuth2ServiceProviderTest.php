<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Service\Provider;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\App\Application;
use Platine\Framework\Service\Provider\OAuth2ServiceProvider;
use Platine\OAuth2\Configuration;
use Platine\OAuth2\Grant\AuthorizationGrant;
use Platine\Route\Router;
use Platine\Test\Framework\Fixture\MyApp;
use Platine\Test\Framework\Fixture\MyOAuthGrant;

/*
 * @group core
 * @group framework
 */
class OAuth2ServiceProviderTest extends PlatineTestCase
{
    public function testRegister(): void
    {
        $app = new MyApp();
        $o = new OAuth2ServiceProvider($app);
        $o->register();

        $this->assertInstanceOf(Configuration::class, $app->get(Configuration::class));
    }

    public function testRegisterWithGrantUsingContainer(): void
    {
        $cfg = $this->getMockInstance(Configuration::class, [
            'getGrants' => [AuthorizationGrant::class]
        ]);
        $app = $this->getMockInstanceMap(Application::class, [
            'get' => [[Configuration::class, $cfg]],
            'has' => [[AuthorizationGrant::class, true]]
        ]);
        $o = new OAuth2ServiceProvider($app);
        $o->register();

        $this->assertInstanceOf(Configuration::class, $app->get(Configuration::class));
    }

    public function testRegisterWithGrantWithoutContainer(): void
    {
        $cfg = $this->getMockInstance(Configuration::class, [
            'getGrants' => [MyOAuthGrant::class]
        ]);
        $app = $this->getMockInstanceMap(Application::class, [
            'get' => [[Configuration::class, $cfg]],
            'has' => [[MyOAuthGrant::class, false]]
        ]);
        $o = new OAuth2ServiceProvider($app);
        $o->register();

        $this->assertInstanceOf(Configuration::class, $app->get(Configuration::class));
    }

    public function testAddRoutes(): void
    {
        $app = $this->getMockInstance(Application::class);
        $router = $this->getMockInstance(Router::class, [], ['group']);

        $router->expects($this->exactly(2))
                ->method('post');

        $router->expects($this->exactly(1))
                ->method('add');

        $o = new OAuth2ServiceProvider($app);
        $o->addRoutes($router);
    }
}
