<?php
    use Platine\Logger\LogLevel;
    
    return [
        'level' => LogLevel::DEBUG,
        'driver' => 'null',
        'handlers' => [
            'file' => [
                'path' => __DIR__ . '/../storage/tmp/logs',
                'prefix' => 'app.',
                'level' => LogLevel::DEBUG,
            ],
            'email' => [
                'from' => 'app@foo.com',
                'engine' => 'smtp',
                'level' => LogLevel::ERROR,
            ]
        
        ]
    ];