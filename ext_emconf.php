<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Store Finder',
    'description' => 'Manage store locations, search by distance and show Google maps.',
    'category' => 'plugin',
    'author' => 'Sebastian Fischer',
    'author_email' => 'typo3@evoweb.de',
    'author_company' => 'evoweb',
    'state' => 'stable',
    'clearCacheOnLoad' => 1,
    'version' => '5.2.0',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-10.4.99',
            'core' => '10.4.0-10.4.99',
            'static_info_tables' => '6.8.7-',
        ],
    ],
];
