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
    protected Application $application;
    protected Router $router;

    /**
     *
     */
    public function __construct(Application $application, Router $router)
    {
        parent::__construct('route', 'Command to manage route');

        $this->addOption('-l|--list', 'Show route list', null, false);

        $this->router = $router;
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
        /** @template T @var Config<T> $config */
        $config = $this->application->get(Config::class);
        $routeList = $config->get('routes', []);
        $routeList[0]($this->router);

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
