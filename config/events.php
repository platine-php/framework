<?php

use Platine\Framework\Auth\Event\AuthInvalidPasswordEvent;
use Platine\Framework\Demo\Event\HandleAuthFailure;

    return [
        AuthInvalidPasswordEvent::class => [
            HandleAuthFailure::class,
        ],
    ];
