<?php

use Platine\Framework\Demo\Template\LangTag;

    return [
        'cache_expire' => 5600,
        'cache_dir' => __DIR__ . '/../storage/cache',
        'cache_prefix' => '__platine_template',
        'template_dir' => __DIR__ . '/../storage/resource/templates',
        'file_extension' => 'html',
        'auto_escape' => true,
        'filters' => [],
        'tags' => [
            'tr' => LangTag::class
        ],
    ];