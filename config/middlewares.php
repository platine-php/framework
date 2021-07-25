<?php

use Platine\Cookie\Middleware\CookieSendMiddleware;
use Platine\Framework\Auth\Middleware\AuthenticationMiddleware;
use Platine\Framework\Auth\Middleware\AuthorizationMiddleware;
use Platine\Framework\Http\Middleware\CsrfMiddleware;
use Platine\Framework\Http\Middleware\ErrorHandlerMiddleware;
use Platine\Framework\Http\Middleware\RouteDispatcherMiddleware;
use Platine\Framework\Http\Middleware\RouteMatchMiddleware;

    return [
        ErrorHandlerMiddleware::class,
        CookieSendMiddleware::class,
        RouteMatchMiddleware::class,
        CsrfMiddleware::class, //Must be after Route match middleware
        AuthenticationMiddleware::class, //Must be after Route match middleware
        AuthorizationMiddleware::class, //Must be after Authentication middleware
        RouteDispatcherMiddleware::class,
    ];
