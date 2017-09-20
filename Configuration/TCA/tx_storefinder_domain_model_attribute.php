<?php

$foreignTypes = [
    '0' => [
        'showitem' => '
--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
--palette--;;filePalette'
    ],
    \TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => [
        'showitem' => '
--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
--palette--;;filePalette'
    ],
    \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
        'showitem' => '
--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
--palette--;;filePalette'
    ],
    \TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => [
        'showitem' => '
--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
--palette--;;filePalette'
    ],
    \TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => [
        'showitem' => '
--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
--palette--;;filePalette'
    ],
    \TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => [
        'showitem' => '
--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
--palette--;;filePalette'
    ]
];

$table = 'tx_storefinder_domain_model_attribute';
$languageFile = 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:';

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
        'selicon_field_path' => 'uploads/tx_storefinder',

        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'iconfile' => 'EXT:store_finder/Resources/Public/Icons/tx_storefinder_domain_model_attribute.gif',
    ],

    'interface' => [
        'showRecordFieldList' => 'sys_language_uid,l18n_parent,l18n_diffsource,name,icon'
    ],

    'columns' => [
        'sys_language_uid' => $GLOBALS['TCA']['tt_content']['columns']['sys_language_uid'],

        'l18n_parent' => [
            'exclude' => true,
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        '',
                        0
                    ]
                ],
                'foreign_table' => $table,
                'foreign_table_where' =>
                    'AND ' . $table . '.pid = ###CURRENT_PID### AND ' . $table . '.sys_language_uid IN (-1, 0)',
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
                        'createNewRelationLinkTitle' => 'LLL:EXT:cms/locallang_ttc.xlf:images.addFileReference'
                    ],
                    'minitems' => 0,
                    'maxitems' => 1,
                    // custom configuration for displaying fields in the overlay/reference table
                    // to use the imageoverlayPalette instead of the basicoverlayPalette
                    'foreign_types' => $foreignTypes,
                ],
                $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
            ),
        ],
    ],

    'types' => [
        '0' => [
            'showitem' => 'sys_language_uid, l18n_parent, l18n_diffsource, name, icon'
        ]
    ],
    'palettes' => [
        'language' => [
            'showitem' => '
            sys_language_uid;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:sys_language_uid_formlabel,
            l18n_parent
            ',
        ],
    ]
];
