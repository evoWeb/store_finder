<?php

$EM_CONF['store_finder'] = [
    'title' => 'Store Finder',
    'description' => 'Manage store locations, search by distance and show Google maps.',
    'category' => 'plugin',
    'author' => 'Sebastian Fischer',
    'author_email' => 'store-finder@evoweb.de',
    'author_company' => 'evoWeb',
    'state' => 'stable',
    'version' => '7.0.11',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-12.4.99',
            'static_info_tables' => '11.5.2-',
        ],
    ],
];
