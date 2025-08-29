<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Service\Provider;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\App\Application;
use Platine\Framework\Config\AppDatabaseConfig;
use Platine\Framework\Config\DatabaseConfigLoaderInterface;
use Platine\Framework\Service\Provider\DatabaseConfigServiceProvider;
use Platine\Test\Framework\Fixture\MyApp;
use Platine\Test\Framework\Fixture\MyDatabaseConfigLoader;

/*
 * @group core
 * @group framework
 */
class DatabaseConfigServiceProviderTest extends PlatineTestCase
{
    public function testRegister(): void
    {
        $app = $this->getMockInstanceMap(Application::class);

        $app->expects($this->exactly(3))
            ->method('bind');

        $o = new DatabaseConfigServiceProvider($app);
        $o->register();
    }

    public function testRegisterShare(): void
    {
        $app = new MyApp();

        $o = new DatabaseConfigServiceProvider($app);
        $o->register();

        $app->bind(DatabaseConfigLoaderInterface::class, MyDatabaseConfigLoader::class);

        $this->assertInstanceOf(AppDatabaseConfig::class, $app->get(AppDatabaseConfig::class));
    }
}
