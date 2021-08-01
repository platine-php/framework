<?php

use Platine\Framework\Demo\API\Provider\AppServiceProvider as APIAppServiceProvider;
use Platine\Framework\Demo\API\Provider\UserServiceProvider as APIUserServiceProvider;
use Platine\Framework\Demo\Provider\AppServiceProvider;
use Platine\Framework\Demo\Provider\PermissionServiceProvider;
use Platine\Framework\Demo\Provider\RepositoryServiceProvider;
use Platine\Framework\Demo\Provider\RoleServiceProvider;
use Platine\Framework\Demo\Provider\UserServiceProvider;
use Platine\Framework\Service\Provider\AuthServiceProvider;
use Platine\Framework\Service\Provider\CacheServiceProvider;
use Platine\Framework\Service\Provider\CommandServiceProvider;
use Platine\Framework\Service\Provider\CookieServiceProvider;
use Platine\Framework\Service\Provider\DatabaseConfigServiceProvider;
use Platine\Framework\Service\Provider\DatabaseServiceProvider;
use Platine\Framework\Service\Provider\ErrorHandlerServiceProvider;
use Platine\Framework\Service\Provider\LangServiceProvider;
use Platine\Framework\Service\Provider\LoggerServiceProvider;
use Platine\Framework\Service\Provider\MigrationServiceProvider;
use Platine\Framework\Service\Provider\PaginationServiceProvider;
use Platine\Framework\Service\Provider\RoutingServiceProvider;
use Platine\Framework\Service\Provider\SecurityServiceProvider;
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
        CommandServiceProvider::class,
        AuthServiceProvider::class,
        PaginationServiceProvider::class,
        SecurityServiceProvider::class,

        //Custom
        APIUserServiceProvider::class,
        APIAppServiceProvider::class,
        AppServiceProvider::class,
        UserServiceProvider::class,
        RoleServiceProvider::class,
        PermissionServiceProvider::class,
        RepositoryServiceProvider::class,
    ];
