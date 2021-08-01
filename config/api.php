<?php

    return [
        'auth' => [
            'path' => '/api',
            'url_whitelist' => [
               '/api/auth/login',
            ],
            'expire' => 3600,
            'headers' => [
                'name' => 'Authorization',
                'token_type' => 'Bearer',
            ]
        ],
        'sign' => [
            'secret' => 'foobar',
            'hmac' => [
                'signature_algo' => 'sha256',
                'token_header_algo' => 'HS256',
            ],
        ]
    ];
