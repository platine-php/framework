<?php

use Platine\Framework\Template\Tag\CsrfTag;
use Platine\Framework\Template\Tag\CurrentUrlTag;
use Platine\Framework\Template\Tag\LangTag;
use Platine\Framework\Template\Tag\RouteUrlTag;
use Platine\Framework\Template\Tag\SessionFlashTag;
use Platine\Framework\Template\Tag\SessionTag;
use Platine\Framework\Template\Tag\StaticTag;

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
            'current_url' => CurrentUrlTag::class,
            'csrf' => CsrfTag::class,
        ],
    ];
