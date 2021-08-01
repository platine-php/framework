<?php

    return [
        'authentication' => [
            'login_route' => 'user_login',
            'url_whitelist' => [
                '/users/login',
                '/users/logout',
                '/api',
            ],
        ],
        'authorization' => [
            'unauthorized_route' => 'home',
        ],
    ];
