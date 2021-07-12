<?php

use Platine\Cookie\Middleware\CookieSendMiddleware;
use Platine\Framework\Http\Middleware\ErrorHandlerMiddleware;
use Platine\Framework\Http\Middleware\RouteDispatcherMiddleware;
use Platine\Framework\Http\Middleware\RouteMatchMiddleware;
    
    return [
        ErrorHandlerMiddleware::class,
        CookieSendMiddleware::class,
        RouteMatchMiddleware::class,
        RouteDispatcherMiddleware::class,
    ];