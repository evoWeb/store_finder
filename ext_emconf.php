<?php

$EM_CONF['store_finder'] = [
    'title' => 'Store Finder',
    'description' => 'Manage store locations, search by distance and show Google maps.',
    'category' => 'plugin',
    'author' => 'Sebastian Fischer',
    'author_email' => 'typo3@evoweb.de',
    'author_company' => 'evoweb',
    'state' => 'stable',
    'clearCacheOnLoad' => 1,
    'version' => '6.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '11.0.0-11.9.99',
            'static_info_tables' => '6.8.7-',
        ],
    ],
];
