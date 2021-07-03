<?php

use Platine\Framework\Demo\Action\User\CreateAction;
use Platine\Framework\Demo\Action\User\DeleteAction;
use Platine\Framework\Demo\Action\User\DetailAction;
use Platine\Framework\Demo\Action\User\EditAction;
use Platine\Framework\Demo\Action\User\ListAction;
use Platine\Framework\Demo\Action\User\LoginAction;
use Platine\Framework\Demo\Action\User\LogoutAction;
use Platine\Route\Router;

    return [static function(Router $router): void{
        $router->get('/list', ListAction::class, 'user_list');
        $router->get('/detail/{id:i}', DetailAction::class, 'user_detail');
        $router->get('/delete/{id:i}', DeleteAction::class, 'user_delete');
        $router->get('/logout', LogoutAction::class, 'logout');
        $router->add('/login', LoginAction::class, ['GET', 'POST'], 'user_login');
        $router->add('/add', CreateAction::class, ['GET', 'POST'], 'user_create');
        $router->add('/edit/{id:i}', EditAction::class, ['GET', 'POST'], 'user_edit');
    }];