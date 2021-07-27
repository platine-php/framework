<?php

use Platine\Framework\Demo\Action\DownloadAction;
use Platine\Framework\Demo\Action\HomeAction;
use Platine\Framework\Demo\Action\JsonAction;
use Platine\Route\Router;

return [static function (Router $router): void {
    $router->get('/', HomeAction::class, 'home');
    $router->get('/download', DownloadAction::class, 'download');
    $router->group('/api', function (Router $router) {
        $router->post('/json', JsonAction::class, 'api_json');
    });
}];
