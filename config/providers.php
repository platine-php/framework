<?php

use Platine\Framework\Demo\Provider\MyServiceProvider;
use Platine\Framework\Demo\Provider\RepositoryServiceProvider;
use Platine\Framework\Demo\Provider\UserServiceProvider;
use Platine\Framework\Service\Provider\CacheServiceProvider;
use Platine\Framework\Service\Provider\CookieServiceProvider;
use Platine\Framework\Service\Provider\DatabaseConfigServiceProvider;
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
        DatabaseServiceProvider::class,
        SessionServiceProvider::class,
        MigrationServiceProvider::class,
        CacheServiceProvider::class,
        TemplateServiceProvider::class,
        CookieServiceProvider::class,
        LangServiceProvider::class,
        DatabaseConfigServiceProvider::class,
        
        //Custom
        MyServiceProvider::class,
        UserServiceProvider::class,
        RepositoryServiceProvider::class,
    ];