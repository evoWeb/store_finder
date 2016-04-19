<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "store_finder".
 * Auto generated 05-08-2013 13:34
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
    'title' => 'Store Finder',
    'description' => 'Manage store locations, search by distance and show Google maps.',
    'category' => 'plugin',
    'shy' => 0,
    'version' => '1.0.2',
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
    'constraints' => array(
        'depends' => array(
            'typo3' => '6.0.0-7.6.99',
            'static_info_tables' => '6.0.4-',
        ),
        'conflicts' => array(),
        'suggests' => array(),
    ),
);
