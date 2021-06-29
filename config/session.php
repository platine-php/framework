<?php
    use Platine\Session\Storage\LocalStorage;
    use Platine\Session\Storage\ApcuStorage;
    use Platine\Session\Storage\NullStorage;

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
                'class' => LocalStorage::class,
                'path' => __DIR__ . '/../storage/tmp/session',
                'prefix' => 'sess_',
            ], 
            'apcu' => [
                'class' => ApcuStorage::class,
            ],
            'null' => [
                'class' => NullStorage::class,
            ],
        ]
    
    ];