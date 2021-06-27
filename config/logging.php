<?php
    use Platine\Logger\LogLevel;
    use Platine\Logger\Handler\FileHandler;
    
    return [
        'level' => LogLevel::DEBUG,
        'handlers' => [
            'file' => [
                'enable' => true,
                'class' => FileHandler::class,
                'path' => __DIR__ . '/../storage/logs',
                'prefix' => 'app.',
                'level' => LogLevel::DEBUG,
            ],
            'email' => [
                'enable' => false,
                //'class' => EmailHandler::class,
                'from' => 'app@foo.com',
                'engine' => 'smtp',
                'level' => LogLevel::ERROR,
            ]
        
        ]
    ];