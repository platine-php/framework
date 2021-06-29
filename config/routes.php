<?php

use Platine\Framework\Demo\Action\User\DetailAction;
use Platine\Framework\Demo\Action\User\ListAction;
use Platine\Framework\Demo\Action\User\LoginAction;
use Platine\Framework\Demo\Action\User\LogoutAction;
use Platine\Route\Router;

    return [static function(Router $router): void{
        $router->get('/list', ListAction::class, 'list');
        $router->get('/detail/{id:i}', DetailAction::class, 'detail');
        $router->get('/logout', LogoutAction::class, 'logout');
        $router->add('/login', LoginAction::class, ['GET', 'POST'], 'login');
    }];