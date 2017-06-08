<?php

$EM_CONF['store_finder'] = [
    'title' => 'Store Finder',
    'description' => 'Manage store locations, search by distance and show Google maps.',
    'category' => 'plugin',
    'author' => 'Sebastian Fischer',
    'author_email' => 'typo3@evoweb.de',
    'author_company' => 'evoweb',
    'state' => 'stable',
    'uploadfolder' => 1,
    'clearcacheonload' => 0,
    'version' => '1.2.1',
    'constraints' => [
        'depends' => [
            'typo3' => '7.6.0-8.7.99',
            'static_info_tables' => '6.4.2-',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
