<?php

    return [
        'authentication' => [
            'login_route' => 'user_login',
            'url_whitelist' => [
                '/users/login',
                '/users/logout',
            ]
        ],
        'authorization' => [
            'unauthorized_route' => 'home',
        ],
    ];
