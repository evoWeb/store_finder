<?php

use Evoweb\StoreFinder\Middleware\StoreFinderMiddleware;

return [
    'frontend' => [
        'evoweb/storefinder-locations' => [
            'target' => StoreFinderMiddleware::class,
            'after' => [
                'typo3/cms-frontend/tsfe',
            ],
            'before' => [
                'typo3/cms-frontend/prepare-tsfe-rendering',
            ],
        ],
    ],
];
