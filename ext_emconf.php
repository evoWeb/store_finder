<?php

$EM_CONF['store_finder'] = [
    'title' => 'Store Finder',
    'description' => 'Manage store locations, search by distance and show Google maps.',
    'category' => 'plugin',
    'author' => 'Sebastian Fischer',
    'author_email' => 'store-finder@evoweb.de',
    'author_company' => 'evoWeb',
    'state' => 'stable',
    'version' => '8.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.1.0-13.4.99',
            'static_info_tables' => '11.5.2-',
        ],
    ],
];
