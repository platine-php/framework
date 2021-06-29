<?php
    use Platine\Cache\Storage\LocalStorage;
    use Platine\Cache\Storage\ApcuStorage;
    use Platine\Cache\Storage\NullStorage;
    
    return [
        'ttl' => 300,
        'driver' => 'apcu',
        'storages' => [
            'file' => [
                'class' => LocalStorage::class,
                'path' => __DIR__ . '/../storage/tmp/cache',
                'prefix' => 'cache_',
            ], 
            'apcu' => [
                'class' => ApcuStorage::class,
            ],
            'null' => [
                'class' => NullStorage::class,
            ],
        ]
    
    ];