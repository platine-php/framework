<?php

    return [
        'authentication' => [
            'auth_route_name' => 'user_login',
            'url_whitelist' => [
                '/users/login',
                '/users/logout',
            ]
        ],
        'authorization' => [
            'unauthorized_route_name' => 'home',
        ],
    ];
