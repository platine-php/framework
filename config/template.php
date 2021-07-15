<?php

use Platine\Framework\Demo\Template\LangTag;
use Platine\Framework\Demo\Template\RouteUrlTag;
use Platine\Framework\Demo\Template\SessionFlashTag;
use Platine\Framework\Demo\Template\SessionTag;
use Platine\Framework\Demo\Template\StaticTag;

    return [
        'cache_expire' => 5600,
        'cache_dir' => __DIR__ . '/../storage/cache',
        'cache_prefix' => '__platine_template',
        'template_dir' => __DIR__ . '/../storage/resource/templates',
        'file_extension' => 'html',
        'auto_escape' => true,
        'filters' => [],
        'tags' => [
            'tr' => LangTag::class,
            'route_url' => RouteUrlTag::class,
            'session' => SessionTag::class,
            'flash' => SessionFlashTag::class,
            'static' => StaticTag::class,
        ],
    ];