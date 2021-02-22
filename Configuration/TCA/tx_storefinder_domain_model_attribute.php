<?php

$overrideChildTca = [
    'types' => [
        '0' => [
            'showitem' => '
                --palette--;;imageoverlayPalette,
                --palette--;;filePalette'
        ],
        \TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => [
            'showitem' => '
                --palette--;;imageoverlayPalette,
                --palette--;;filePalette'
        ],
        \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
            'showitem' => '
                --palette--;;imageoverlayPalette,
                --palette--;;filePalette'
        ],
        \TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => [
            'showitem' => '
                --palette--;;audioOverlayPalette,
                --palette--;;filePalette'
        ],
        \TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => [
            'showitem' => '
                --palette--;;videoOverlayPalette,
                --palette--;;filePalette'
        ],
        \TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => [
            'showitem' => '
                --palette--;;imageoverlayPalette,
                --palette--;;filePalette'
        ]
    ],
];

$languageFile = 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xlf:';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages(
    'tx_storefinder_domain_model_attribute'
);

return [
    'ctrl' => [
        'title' => $languageFile . 'tx_storefinder_domain_model_attribute',
        'label' => 'name',
        'label_alt' => 'icon',
        'label_alt_force' => '1',
        'sortby' => 'sorting',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',

        'transOrigPointerField' => 'l18n_parent',
        'transOrigDiffSourceField' => 'l18n_diffsource',
        'languageField' => 'sys_language_uid',
        'translationSource' => 'l10n_source',

        'selicon_field' => 'icon',

        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'iconfile' => 'EXT:store_finder/Resources/Public/Icons/tx_storefinder_domain_model_attribute.gif',
    ],

    'columns' => [
        'sys_language_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'special' => 'languages',
                'items' => [
                    [
                        'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages',
                        -1,
                        'flags-multiple'
                    ],
                ],
                'default' => 0,
            ]
        ],
        'l18n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        '',
                        0
                    ]
                ],
                'foreign_table' => 'tx_storefinder_domain_model_attribute',
                // no sys_language_uid = -1 allowed explicitly!
                'foreign_table_where' =>
                    'AND tx_storefinder_domain_model_attribute.pid = ###CURRENT_PID###'
                    . ' AND tx_storefinder_domain_model_attribute.sys_language_uid = 0',
                'default' => 0
            ]
        ],
        'l10n_source' => [
            'config' => [
                'type' => 'passthrough'
            ]
        ],
        'l18n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
                'default' => ''
            ]
        ],
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.visible',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        0 => '',
                        1 => '',
                        'invertStateDisplay' => true
                    ]
                ],
            ]
        ],

        'name' => [
            'label' => $languageFile . 'tx_storefinder_domain_model_attribute.name',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'max' => 255,
                'eval' => 'required,trim',
            ]
        ],

        'icon' => [
            'label' => $languageFile . 'tx_storefinder_domain_model_attribute.icon',
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'icon',
                [
                    'appearance' => [
                        'createNewRelationLinkTitle' =>
                            'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:images.addFileReference'
                    ],
                    'minitems' => 0,
                    'maxitems' => 1,
                    // custom configuration for displaying fields in the overlay/reference table
                    // to use the imageoverlayPalette instead of the basicoverlayPalette
                    'overrideChildTca' => $overrideChildTca,
                ],
                $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
            ),
        ],
    ],

    'types' => [
        '0' => [
            'showitem' => '
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    name, icon,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                    --palette--;;language,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                    --palette--;;hidden,
            '
        ]
    ],
    'palettes' => [
        'language' => [
            'showitem' => '
                sys_language_uid;'
                . 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:sys_language_uid_formlabel,
                l18n_parent
            ',
        ],
        'hidden' => [
            'showitem' => '
                hidden;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:field.default.hidden
            ',
        ],
    ]
];
