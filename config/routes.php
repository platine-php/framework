<?php

use Platine\Framework\Demo\Action\HomeAction;
use Platine\Framework\Demo\Action\LoginAction;
use Platine\Framework\Demo\Action\LogoutAction;
use Platine\Route\Router;

    return [static function(Router $router): void{
        $router->get('/home', HomeAction::class, 'home');
        $router->get('/logout', LogoutAction::class, 'logout');
        $router->add('/login', LoginAction::class, ['GET', 'POST'], 'login');
    }];