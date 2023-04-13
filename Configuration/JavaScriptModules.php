<?php

return [
    'dependencies' => [
        'backend',
        'core',
    ],
    'tags' => [
        'backend.form',
    ],
    'imports' => [
        '@evoweb/store-finder/' => [
            'path' => 'EXT:store_finder/Resources/Public/JavaScript/',
        ],
    ],
];
