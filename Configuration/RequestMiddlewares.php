<?php

use Evoweb\StoreFinder\Middleware\CategoryMiddleware;
use Evoweb\StoreFinder\Middleware\LocationMiddleware;

return [
    'frontend' => [
        'evoweb/storefinder-categories' => [
            'target' => CategoryMiddleware::class,
            'after' => [
                'typo3/cms-frontend/page-resolver',
            ],
            'before' => [
                'typo3/cms-adminpanel/sql-logging',
            ],
        ],
        'evoweb/storefinder-locations' => [
            'target' => LocationMiddleware::class,
            'after' => [
                'typo3/cms-frontend/prepare-tsfe-rendering',
            ],
            'before' => [
                'typo3/cms-frontend/shortcut-and-mountpoint-redirect',
            ],
        ],
    ],
];
