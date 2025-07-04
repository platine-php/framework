<?php

declare(strict_types=1);

namespace Platine\Test\Framework\App;

use InvalidArgumentException;
use org\bovigo\vfs\vfsStream;
use Platine\Config\Config;
use Platine\Config\FileLoader;
use Platine\Dev\PlatineTestCase;
use Platine\Event\DispatcherInterface;
use Platine\Event\EventInterface;
use Platine\Filesystem\Adapter\AdapterInterface;
use Platine\Filesystem\Adapter\Local\LocalAdapter;
use Platine\Filesystem\Filesystem;
use Platine\Framework\App\Application;
use Platine\Framework\Env\Env;
use Platine\Framework\Helper\Timer\Watch;
use Platine\Framework\Http\Maintenance\Driver\FileMaintenanceDriver;
use Platine\Test\Framework\Fixture\MyApp;
use Platine\Test\Framework\Fixture\MyCommand;
use Platine\Test\Framework\Fixture\MyEventListener;
use Platine\Test\Framework\Fixture\MyEventSubscriber;
use Platine\Test\Framework\Fixture\MyServiceProvider;
use Platine\Test\Framework\Fixture\MyTask;

/*
 * @group core
 * @group framework
 */
class ApplicationTest extends PlatineTestCase
{
    public function testConstructor(): void
    {
        $app = new Application('');
        $this->assertInstanceOf(Application::class, $app);
    }

    public function testVersion(): void
    {
        $app = new Application('');
        $this->assertEquals('2.0.0-dev', $app->version());
    }

    public function testGetSetAll(): void
    {
        $app = new Application('');
        $app->setAppPath('/app');
        $app->setBasePath('/basepath');
        $app->setConfigPath('/configpath');
        $app->setEnvironment('staging');
        $app->setNamespace('MyApp');
        $app->setStoragePath('/storagepath');
        $app->setVendorPath('/vendorpath');
        $app->setRootPath('/rootpath');
        $app->setEnvironmentFile('.envfile');
        $this->assertEquals('/app', $app->getAppPath());
        $this->assertEquals('/basepath', $app->getBasePath());
        $this->assertEquals('staging', $app->getEnvironment());
        $this->assertEquals('/configpath', $app->getConfigPath());
        $this->assertEquals('MyApp', $app->getNamespace());
        $this->assertEquals('/storagepath', $app->getStoragePath());
        $this->assertEquals('/vendorpath', $app->getVendorPath());
        $this->assertEquals('/rootpath', $app->getRootPath());
        $this->assertEquals('.envfile', $app->getEnvironmentFile());
    }

    public function testDispatchAndListenUsingCallable(): void
    {
        $eventName = 'fooevent';
        $app = new Application('');
        /** @var DispatcherInterface $dispatcher */
        $dispatcher = $this->getPropertyValue(Application::class, $app, 'dispatcher');
        $this->assertEmpty($dispatcher->getListeners());
        $app->listen($eventName, function (EventInterface $e) {
            echo $e->getName();
        });
        $this->assertCount(1, $dispatcher->getListeners());
        $this->assertCount(1, $dispatcher->getListeners($eventName));

        $app->dispatch($eventName);
        $this->expectOutputString($eventName);
    }

    public function testDispatchAndListenUsingContainer(): void
    {
        $eventName = 'fooevent';
        $app = new Application('');
        $app->bind(MyEventListener::class);
        $app->listen($eventName, MyEventListener::class);

        $app->dispatch($eventName);
        $this->expectOutputString($eventName);
    }

    public function testDispatchAndListenUsingClassInstance(): void
    {
        $eventName = 'fooevent';
        $app = new Application('');
        $app->listen($eventName, MyEventListener::class);

        $app->dispatch($eventName);
        $this->expectOutputString($eventName);
    }

    public function testDispatchAndListenUsingSubscriber(): void
    {
        $eventName = 'fooevent';
        $app = new Application('');
        $app->subscribe(new MyEventSubscriber());

        $app->dispatch($eventName);
        $this->expectOutputString($eventName);
    }

    public function testListenListenerNotExist(): void
    {
        $eventName = 'fooevent';
        $app = new Application('');
        $this->expectException(InvalidArgumentException::class);
        $app->listen($eventName, 'invalid_listener_not_found');
    }

    public function testProviders(): void
    {
        $app = new Application('');
        $app->registerServiceProvider(MyServiceProvider::class, false);
        //we already registered two bases services providers
        // so the value will be 3 instead of 1
        $this->assertCount(3, $app->getProviders());

        $commands = $app->getProvidersCommands();
        $tasks = $app->getProvidersTasks();
        $this->assertCount(1, $commands);
        $this->assertCount(1, $tasks);
        $this->assertEquals(MyCommand::class, $commands[0]);
        $this->assertEquals(MyTask::class, $tasks[0]);
    }

    public function testBoot(): void
    {
        $app = new Application('');
        $app->registerServiceProvider(MyServiceProvider::class, false);
        $app->boot();
        $this->expectOutputString(MyServiceProvider::class . '::boot');
    }

    public function testBootAlreadyBootBefore(): void
    {
        $app = new Application('');
        $app->registerServiceProvider(MyServiceProvider::class, false);
        $this->setPropertyValue(Application::class, $app, 'booted', true);
        $app->boot();
        $this->expectOutputString('');
    }

    public function testRegisterServiceProviderNoForce(): void
    {
        $app = new Application('');
        $app->registerServiceProvider(MyServiceProvider::class, false);
        $provider = $app->getServiceProvider(MyServiceProvider::class);
        $this->assertInstanceOf(MyServiceProvider::class, $provider);
        $this->assertEquals(
            $app->getServiceProvider(MyServiceProvider::class),
            $provider
        );
        $app->registerServiceProvider(MyServiceProvider::class, false);
        $this->assertEquals(
            $app->getServiceProvider(MyServiceProvider::class),
            $provider
        );
    }

    public function testRegisterServiceProviderForce(): void
    {
        $app = new Application('');
        $app->registerServiceProvider(MyServiceProvider::class, false);
        $provider = $app->getServiceProvider(MyServiceProvider::class);
        $this->assertInstanceOf(MyServiceProvider::class, $provider);
        $this->assertEquals(
            $app->getServiceProvider(MyServiceProvider::class),
            $provider
        );
        $app->registerServiceProvider(MyServiceProvider::class, true);
        $provider2 = $app->getServiceProvider(MyServiceProvider::class);
        $this->assertTrue($provider !== $provider2);
    }

    public function testRegisterServiceProviderAlreadyBooted(): void
    {
        $app = new Application('');
        $this->setPropertyValue(Application::class, $app, 'booted', true);
        $app->registerServiceProvider(MyServiceProvider::class, false);
        $this->expectOutputString(MyServiceProvider::class . '::boot');
    }

    public function testRegisterConfiguredServiceProviders(): void
    {
        $config = $this->getMockInstance(Config::class, [
            'get' => [
                MyServiceProvider::class
            ]
        ]);
        $app = new Application('');
        $app->instance($config, Config::class);
        $this->assertCount(2, $app->getProviders());
        $app->registerConfiguredServiceProviders();
        $this->assertCount(3, $app->getProviders());
    }

    public function testRegisterEnvironmentVariables(): void
    {
        $vfsRoot = vfsStream::setup();
        $vfsPath = vfsStream::newDirectory('my_tests')->at($vfsRoot);
        $this->createVfsFile('.env', $vfsPath, 'foo=bar');
        $app = new Application('');
        $app->setRootPath($vfsPath->url());
        $app->registerEnvironmentVariables();
        $this->assertEquals('bar', Env::get('foo'));
    }

    public function testRegisterConfiguredEvents(): void
    {
        $config = $this->getMockInstance(Config::class, [
            'get' => [
                'fooevent' => [
                    MyEventListener::class
                ]
            ]
        ]);
        $app = new Application('');
        $app->instance($config, Config::class);
        $app->registerConfiguredEvents();
        $app->dispatch('fooevent');
        $this->expectOutputString('fooevent');
    }

    public function testRegisterConfiguration(): void
    {
        $app = new Application('');
        $this->assertFalse($app->has(FileLoader::class));
        $this->assertFalse($app->has(Config::class));
        $app->registerConfiguration();
        $this->assertTrue($app->has(FileLoader::class));
        $this->assertTrue($app->has(Config::class));
    }

    public function testMaintenance(): void
    {
        $app = new MyApp('');
        $app->bind(AdapterInterface::class, LocalAdapter::class);
        $app->bind(Filesystem::class);
        $this->assertInstanceOf(FileMaintenanceDriver::class, $app->maintenance());
        $this->assertFalse($app->isInMaintenance());
    }

    public function testWatch(): void
    {
        $app = new MyApp('');
        $this->assertInstanceOf(Watch::class, $app->watch());
    }
}
