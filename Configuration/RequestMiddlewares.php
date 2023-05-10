<?php

use Evoweb\StoreFinder\Middleware\CategoryMiddleware;
use Evoweb\StoreFinder\Middleware\LocationMiddleware;

return [
    'frontend' => [
        'evoweb/storefinder-categories' => [
            'target' => CategoryMiddleware::class,
            'after' => [
                'typo3/cms-frontend/static-route-resolver',
            ],
            'before' => [
                'typo3/cms-frontend/page-resolver',
            ],
        ],
        'evoweb/storefinder-locations' => [
            'target' => LocationMiddleware::class,
            'after' => [
                'typo3/cms-frontend/static-route-resolver',
            ],
            'before' => [
                'typo3/cms-frontend/page-resolver',
            ],
        ],
    ],
];
