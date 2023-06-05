<?php

$EM_CONF['store_finder'] = [
    'title' => 'Store Finder',
    'description' => 'Manage store locations, search by distance and show Google maps.',
    'category' => 'plugin',
    'author' => 'Sebastian Fischer',
    'author_email' => 'store-finder@evoweb.de',
    'author_company' => 'evoWeb',
    'state' => 'stable',
    'version' => '7.0.6',
    'constraints' => [
        'depends' => [
            'typo3' => '12.2.0-12.9.99',
            'static_info_tables' => '11.5.2-',
        ],
    ],
];
