<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Platine\Framework\Demo\Command;

use Platine\Config\Config;
use Platine\Console\Command\Command;
use Platine\Framework\App\Application;
use Platine\Framework\Service\ServiceProvider;
use Platine\Route\Route;
use Platine\Route\Router;
use Platine\Stdlib\Helper\Str;

/**
 * Description of RouteCommand
 *
 * @author tony
 */
class RouteCommand extends Command
{
    protected Config $config;
    protected Router $router;
    protected Application $application;

    /**
     *
     */
    public function __construct(Application $application, Config $config, Router $router)
    {
        parent::__construct('route', 'Command to manage route');

        $this->addOption('-l|--list', 'Show route list', null, false);

        $this->router = $router;
        $this->config = $config;
        $this->application = $application;
    }

    public function execute()
    {
        if ($this->getOptionValue('list')) {
            $this->showRouteList();
        }
    }

    protected function showRouteList(): void
    {
        $writer = $this->io()->writer();

        $writer->boldGreen('ROUTE LIST', true)->eol();
        $routeList = $this->config->get('routes', []);
        $routeList[0]($this->router);

        //Load providers routes
        /** @var ServiceProvider[] $providers */
        $providers = $this->application->getProviders();
        foreach ($providers as $provider) {
            $provider->addRoutes($this->router);
        }

        $routes = $this->router->routes()->all();
        $rows = [];
        foreach ($routes as /** @var Route $route */ $route) {
            $handler = Str::stringify($route->getHandler());
            $rows[] = [
                'name' => $route->getName(),
                'method' => implode('|', $route->getMethods()),
                'path' => $route->getPattern(),
                'handler' => $handler,
            ];
        }

        $writer->table($rows);

        $writer->green('Command finished successfully')->eol();
    }
}
