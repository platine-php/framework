<?php

use Platine\Framework\Demo\Provider\ActionServiceProvider;
use Platine\Framework\Demo\Provider\MyServiceProvider;
use Platine\Framework\Demo\Provider\RepositoryServiceProvider;
use Platine\Framework\Service\Provider\CacheServiceProvider;
use Platine\Framework\Service\Provider\CookieServiceProvider;
use Platine\Framework\Service\Provider\DatabaseServiceProvider;
use Platine\Framework\Service\Provider\ErrorHandlerServiceProvider;
use Platine\Framework\Service\Provider\LangServiceProvider;
use Platine\Framework\Service\Provider\RoutingServiceProvider;
use Platine\Framework\Service\Provider\SessionServiceProvider;
use Platine\Framework\Service\Provider\TemplateServiceProvider;
    
    return [
        //Framework
        ErrorHandlerServiceProvider::class,
        RoutingServiceProvider::class,
        SessionServiceProvider::class,
        DatabaseServiceProvider::class,
        CacheServiceProvider::class,
        TemplateServiceProvider::class,
        CookieServiceProvider::class,
        LangServiceProvider::class,
        
        //Custom
        MyServiceProvider::class,
        ActionServiceProvider::class,
        RepositoryServiceProvider::class,
    ];