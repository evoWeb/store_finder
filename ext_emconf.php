<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "store_finder".
 * Auto generated 05-08-2013 13:34
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF['store_finder'] = [
    'title' => 'Store Finder',
    'description' => 'Manage store locations, search by distance and show Google maps.',
    'category' => 'plugin',
    'shy' => 0,
    'version' => '1.3.0',
    'dependencies' => '',
    'conflicts' => '',
    'priority' => '',
    'loadOrder' => '',
    'module' => '',
    'state' => 'stable',
    'uploadfolder' => 1,
    'createDirs' => '',
    'modify_tables' => '',
    'clearcacheonload' => 0,
    'lockType' => '',
    'author' => 'Sebastian Fischer',
    'author_email' => 'typo3@evoweb.de',
    'author_company' => 'Evoweb',
    'constraints' => [
        'depends' => [
            'typo3' => '7.6.0-7.6.99',
            'static_info_tables' => '6.0.4-',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
