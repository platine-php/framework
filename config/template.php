<?php

    return [
        'cache_expire' => 5600,
        'cache_dir' => __DIR__ . '/../storage/cache',
        'cache_prefix' => '__template_',
        'template_dir' => __DIR__ . '/../storage/resource/templates',
        'file_extension' => 'html',
        'include_with_extension' =>  false,
        'auto_escape' => true,
        'filters' => [],
        'tags' => [],
    ];