<?php

use Platine\Framework\Demo\Provider\MyServiceProvider;
use Platine\Framework\Demo\Provider\RepositoryServiceProvider;
use Platine\Framework\Demo\Provider\UserActionServiceProvider;
use Platine\Framework\Service\Provider\CacheServiceProvider;
use Platine\Framework\Service\Provider\CookieServiceProvider;
use Platine\Framework\Service\Provider\DatabaseServiceProvider;
use Platine\Framework\Service\Provider\ErrorHandlerServiceProvider;
use Platine\Framework\Service\Provider\LangServiceProvider;
use Platine\Framework\Service\Provider\LoggerServiceProvider;
use Platine\Framework\Service\Provider\MigrationServiceProvider;
use Platine\Framework\Service\Provider\RoutingServiceProvider;
use Platine\Framework\Service\Provider\SessionServiceProvider;
use Platine\Framework\Service\Provider\TemplateServiceProvider;
    
    return [
        //Framework
        LoggerServiceProvider::class,
        ErrorHandlerServiceProvider::class,
        RoutingServiceProvider::class,
        SessionServiceProvider::class,
        DatabaseServiceProvider::class,
        MigrationServiceProvider::class,
        CacheServiceProvider::class,
        TemplateServiceProvider::class,
        CookieServiceProvider::class,
        LangServiceProvider::class,
        
        //Custom
        MyServiceProvider::class,
        UserActionServiceProvider::class,
        RepositoryServiceProvider::class,
    ];