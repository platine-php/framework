<?php

    return [
        'ttl' => 300,
        'driver' => 'file',
        'storages' => [
            'file' => [
                'path' => __DIR__ . '/../storage/tmp/cache',
                'prefix' => 'cache_',
            ],
            'apcu' => [],
            'null' => [],
        ]

    ];
