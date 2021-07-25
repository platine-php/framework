<?php

    return [
        'name' => 'PLATINE',
        'debug' => true,
        'env' => 'dev',
        'url' => 'http://localhost/platine-php/packages/framework/public',
        'base_path' => '/platine-php/packages/framework/public',
        'static_dir' => 'static',
        'response_chunck_size' => null,
        'timezone' => 'Africa/Bangui',
        'csrf' => [
            'key' => 'csrf_key',
            'expire' => 600,
            'http_methods' => ['POST', 'PUT', 'DELETE'],
            'url_whitelist' => [
                '/api',
            ]
        ]
    ];
