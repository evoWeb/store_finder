<?php

return [
    'frontend' => [
        'evoweb/storefinder-categories' => [
            'target' => \Evoweb\StoreFinder\Middleware\CategoryMiddleware::class,
            'before' => [
                'shortcut-and-mountpoint-redirect',
            ],
            'after' => [
                'prepare-tsfe-rendering',
            ],
        ],
        'evoweb/storefinder-locations' => [
            'target' => \Evoweb\StoreFinder\Middleware\LocationMiddleware::class,
            'before' => [
                'shortcut-and-mountpoint-redirect',
            ],
            'after' => [
                'prepare-tsfe-rendering',
            ],
        ],
    ],
];
