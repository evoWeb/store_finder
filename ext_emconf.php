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
    'version' => '2.2.0',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-9.5.99',
            'static_info_tables' => '6.5.1-',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    "autoload-dev" => [
        "psr-4" => [
            "Evoweb\\StoreFinder\\Tests\\" => "Tests/",
        ],
    ],
];
