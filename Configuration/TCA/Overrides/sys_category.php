<?php

$tempColumns = [
    'children' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:sys_category.children',
        'config' => [
            'type' => 'inline',
            'foreign_table' => 'sys_category',
            'foreign_field' => 'parent',
        ]
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_category', $tempColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('sys_category', 'children', '', 'after:parent');
