<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Helper;

use Platine\Config\Config;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Auth\Authorization\DefaultAuthorization;
use Platine\Framework\Helper\Sidebar;
use Platine\Framework\Security\Csrf\CsrfManager;
use Platine\Http\Uri;
use Platine\Lang\Lang;
use Platine\Route\Route;
use Platine\Route\RouteCollection;
use Platine\Route\Router;

class SidebarTest extends PlatineTestCase
{
    public function testConstructor(): void
    {
        $router = $this->getMockInstance(Router::class);
        $lang = $this->getMockInstance(Lang::class);
        $csrfManager = $this->getMockInstance(CsrfManager::class);
        $config = $this->getMockInstance(Config::class);
        $authorization = $this->getMockInstance(DefaultAuthorization::class);
        $o = new Sidebar($router, $lang, $csrfManager, $config, $authorization);

        $this->assertInstanceOf(Sidebar::class, $o);
    }

    public function testAddWithoutPermission(): void
    {
        $uri = new Uri('http://localhost/user/create');
        $route = new Route(
            '/user/create',
            '',
            'user_create',
            [],
            ['permission' => null, 'csrf' => true]
        );
        $routeCollection = new RouteCollection([$route]);

        $router = $this->getMockInstance(Router::class, [
            'routes' => $routeCollection,
            'getUri' => $uri,
        ]);
        $lang = $this->getMockInstance(Lang::class);
        $csrfManager = $this->getMockInstance(CsrfManager::class, [
            'getTokenQuery' => ['_token' => 'foobar']]);
        $config = $this->getMockInstance(Config::class, ['get' => 'primary']);
        $authorization = $this->getMockInstance(DefaultAuthorization::class);
        $o = new Sidebar($router, $lang, $csrfManager, $config, $authorization);

        $this->assertEmpty($o->render());

        // Add default
        $o->add('', 'Create User', 'user_create', [], ['confirm' => true]);
        $this->assertEquals(
            '<div class="list-group page-sidebar">'
                . '<a href="#" class="sidebar-action list-group-item list-group-item-primary">'
                . '<b>Actions</b></a>'
                . '<a  class="list-group-item list-group-item-action" '
                . 'href="http://localhost/user/create?_token=foobar">Create User</a></div>',
            $o->render()
        );
    }

    public function testAddWithPermission(): void
    {
        $uri = new Uri('http://localhost/user/create');
        $route = new Route(
            '/user/create',
            '',
            'user_create',
            [],
            ['permission' => 'user_create', 'csrf' => true]
        );
        $routeCollection = new RouteCollection([$route]);

        $router = $this->getMockInstance(Router::class, [
            'routes' => $routeCollection,
            'getUri' => $uri,
        ]);
        $lang = $this->getMockInstance(Lang::class);
        $csrfManager = $this->getMockInstance(CsrfManager::class, [
            'getTokenQuery' => ['_token' => 'foobar']]);
        $config = $this->getMockInstance(Config::class, ['get' => 'primary']);
        $authorization = $this->getMockInstance(DefaultAuthorization::class, [
            'isGranted' => false]);
        $o = new Sidebar($router, $lang, $csrfManager, $config, $authorization);

        $this->assertEmpty($o->render());

        // Add default
        $o->add('', 'Create User', 'user_create', [], ['confirm' => true]);
        $this->assertEmpty($o->render());
    }
}
