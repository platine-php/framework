<?php
    use Platine\Framework\Http\Middleware\RouteDispatcherMiddleware;
    use Platine\Framework\Http\Middleware\RouteMatchMiddleware;
    use Platine\Framework\Http\Middleware\ErrorHandlerMiddleware;
    use Platine\Framework\Http\Middleware\SessionMiddleware;
    use Platine\Cookie\Middleware\CookieSendMiddleware;
    
    return [
        ErrorHandlerMiddleware::class,
        CookieSendMiddleware::class,
        SessionMiddleware::class,
        RouteMatchMiddleware::class,
        RouteDispatcherMiddleware::class,
    ];