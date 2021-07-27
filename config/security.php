<?php

    return [
        'csrf' => [
            'key' => 'csrf_key',
            'expire' => 600,
            'http_methods' => ['POST', 'PUT', 'DELETE'],
            'url_whitelist' => [
                '/api',
            ]
        ],
        'cors' => [
            'origin' => '*',
            'headers' => [
                'Origin',
                'X-Requested-With',
                'Content-Type',
                'Accept',
                'Connection',
                'User-Agent',
                'Cookie',
                'Cache-Control',
                'token',
            ],
            'methods' => ['GET', 'OPTIONS', 'HEAD', 'PUT', 'POST', 'DELETE'],
            'credentials' => true,
            'max_age' => 1800,
        ]
    ];
