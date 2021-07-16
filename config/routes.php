<?php

use Platine\Framework\Demo\Action\HomeAction;
use Platine\Route\Router;

return [static function (Router $router): void {
    $router->get('/', HomeAction::class, 'home');
}];
