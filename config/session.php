<?php
    return [
        'name' => 'PHPSESSID',
        'driver' => 'file',
        'ttl' => 300,
        'flash_key' => 'session_flash',
        'cookie' => [
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => false,
        ],
        'storages' => [
            'file' => [
                'path' => __DIR__ . '/../storage/tmp/session',
                'prefix' => 'sess_',
            ], 
            'apcu' => [],
            'null' => [],
        ]
    
    ];