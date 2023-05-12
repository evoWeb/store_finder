<?php

use Evoweb\StoreFinder\Middleware\StoreFinderMiddleware;

return [
    'frontend' => [
        'evoweb/storefinder-locations' => [
            'target' => StoreFinderMiddleware::class,
            'after' => [
                'typo3/cms-frontend/prepare-tsfe-rendering',
            ],
            'before' => [
                'typo3/cms-frontend/shortcut-and-mountpoint-redirect',
            ],
        ],
    ],
];
