<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

$tempColumns = [
    'children' => [
        'exclude' => 1,
        'label' => 'store_finder.db:sys_category.children',
        'config' => [
            'type' => 'inline',
            'foreign_table' => 'sys_category',
            'foreign_field' => 'parent',
        ],
    ],
    'import_id' => [
        'config' => [
            'type' => 'input',
            'max' => 100,
        ],
    ],
];

ExtensionManagementUtility::addTCAcolumns('sys_category', $tempColumns);
ExtensionManagementUtility::addToAllTCAtypes('sys_category', 'children', '', 'after:parent');
