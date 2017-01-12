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

$languageFile = 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:';

return [
    'ctrl' => [
        'title' => $languageFile . 'tx_storefinder_domain_model_location',
        'label' => 'name',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'sortby' => 'sorting',
        'delete' => 'deleted',

        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l18n_parent',
        'transOrigDiffSourceField' => 'l18n_diffsource',

        'requestUpdate' => 'country',
        'searchFields' => 'name, zipcode, city, address, country, notes',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
            'fe_group' => 'fe_group',
        ],
        'dividers2tabs' => 1,
        'iconfile' => '../typo3conf/ext/store_finder/Resources/Public/Icons/tx_storefinder_domain_model_location.gif',
    ],

    'interface' => [
        'showRecordFieldList' => 'hidden, endtime, fe_group, name, storeid, address, additionaladdress, person, city,
            state, zipcode, country, attributes, products, phone, mobile, hours, url, notes, image, icon, content,
            use_coordinate, categories, latitude, longitude, geocode'
    ],

    'columns' => [
        'hidden' => $GLOBALS['TCA']['tt_content']['columns']['hidden'],
        'starttime' => $GLOBALS['TCA']['tt_content']['columns']['starttime'],
        'endtime' => $GLOBALS['TCA']['tt_content']['columns']['endtime'],
        'fe_group' => $GLOBALS['TCA']['tt_content']['columns']['fe_group'],
        'sys_language_uid' => $GLOBALS['TCA']['tt_content']['columns']['sys_language_uid'],

        'l18n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'items' => [
                    [
                        '',
                        0
                    ]
                ],
                'foreign_table' => 'tx_storefinder_domain_model_location',
                'foreign_table_where' => 'AND tx_storefinder_domain_model_location.pid=###CURRENT_PID### 
                    AND tx_storefinder_domain_model_location.sys_language_uid IN (-1,0)'
            ]
        ],

        // address
        'name' => [
            'l10n_mode' => 'mergeIfNotBlank',
            'exclude' => 0,
            'label' => $languageFile . 'tx_storefinder_domain_model_location.name',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'max' => '256',
                'eval' => 'required,trim',
            ]
        ],

        'storeid' => [
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $languageFile . 'tx_storefinder_domain_model_location.storeid',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'eval' => 'trim',
            ]
        ],

        'address' => [
            'l10n_mode' => 'mergeIfNotBlank',
            'exclude' => 0,
            'label' => $languageFile . 'tx_storefinder_domain_model_location.address',
            'config' => [
                'type' => 'text',
                'cols' => '30',
                'rows' => '3',
                'eval' => 'trim',
            ]
        ],

        'additionaladdress' => [
            'l10n_mode' => 'mergeIfNotBlank',
            'exclude' => 0,
            'label' => $languageFile . 'tx_storefinder_domain_model_location.additionaladdress',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'eval' => 'trim',
            ]
        ],

        'zipcode' => [
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $languageFile . 'tx_storefinder_domain_model_location.zipcode',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'eval' => 'required,trim',
            ]
        ],

        'city' => [
            'l10n_mode' => 'mergeIfNotBlank',
            'exclude' => 0,
            'label' => $languageFile . 'tx_storefinder_domain_model_location.city',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'eval' => 'required,trim',
            ]
        ],

        'state' => [
            'l10n_mode' => 'mergeIfNotBlank',
            'exclude' => 0,
            'label' => $languageFile . 'tx_storefinder_domain_model_location.state',
            'displayCond' => 'FIELD:country:>:0',
            'config' => [
                'type' => 'select',
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => 'static_country_zones',
                'foreign_table_where' => 'AND zn_country_uid = ###REC_FIELD_country### 
                    ORDER BY static_country_zones.zn_name_local',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1
            ]
        ],

        'country' => [
            'l10n_mode' => 'mergeIfNotBlank',
            'exclude' => 0,
            'label' => $languageFile . 'tx_storefinder_domain_model_location.country',
            'config' => [
                'type' => 'select',
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => 'static_countries',
                'itemsProcFunc' => \SJBR\StaticInfoTables\Hook\Backend\Form\ElementRenderingHelper::class
                    . '->translateCountriesSelector',
                'size' => 1,
                'minitems' => 1,
                'maxitems' => 1
            ]
        ],

        // contact
        'person' => [
            'l10n_mode' => 'mergeIfNotBlank',
            'exclude' => 0,
            'label' => $languageFile . 'tx_storefinder_domain_model_location.person',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'eval' => 'trim',
            ]
        ],

        'phone' => [
            'l10n_mode' => 'mergeIfNotBlank',
            'exclude' => 0,
            'label' => $languageFile . 'tx_storefinder_domain_model_location.phone',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'eval' => 'trim',
            ]
        ],

        'mobile' => [
            'l10n_mode' => 'mergeIfNotBlank',
            'exclude' => 0,
            'label' => $languageFile . 'tx_storefinder_domain_model_location.mobile',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'eval' => 'trim',
            ]
        ],

        'fax' => [
            'l10n_mode' => 'mergeIfNotBlank',
            'exclude' => 0,
            'label' => $languageFile . 'tx_storefinder_domain_model_location.fax',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'eval' => 'trim',
            ]
        ],

        'email' => [
            'l10n_mode' => 'mergeIfNotBlank',
            'exclude' => 0,
            'label' => $languageFile . 'tx_storefinder_domain_model_location.email',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'eval' => 'trim',
            ]
        ],

        'hours' => [
            'l10n_mode' => 'mergeIfNotBlank',
            'exclude' => 0,
            'label' => $languageFile . 'tx_storefinder_domain_model_location.hours',
            'config' => [
                'type' => 'text',
                'cols' => '30',
                'rows' => '5',
            ]
        ],


        // relations
        'related' => [
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $languageFile . 'tx_storefinder_domain_model_location.related',
            'config' => [
                'type' => 'select',
                'items' => [
                    ['', 0]
                ],
                'foreign_table' => 'tx_storefinder_domain_model_location',
                'foreign_table_where' => 'AND tx_storefinder_domain_model_location.uid != ###THIS_UID###
                    ORDER BY tx_storefinder_domain_model_location.name',
                'MM' => 'sys_category_record_mm',
                'MM_match_fields' => [
                    'tablenames' => 'tx_storefinder_domain_model_location',
                    'fieldname' => 'related',
                ],
                'minitems' => '0',
                'maxitems' => '1',
                'default' => '0',
            ]
        ],

        'categories' => [
            'l10n_mode' => 'exclude',
            'label' => $languageFile . 'tx_storefinder_domain_model_location.categories',
            'config' => [
                'type' => 'select',
                'foreign_table' => 'sys_category',
                'foreign_table_where' => 'AND sys_category.sys_language_uid IN (-1,0) ORDER BY sys_category.title ASC',
                'MM' => 'sys_category_record_mm',
                'MM_opposite_field' => 'items',
                'MM_match_fields' => [
                    'tablenames' => 'tx_storefinder_domain_model_location',
                    'fieldname' => 'categories',
                ],
                'size' => 10,
                'autoSizeMax' => 50,
                'maxitems' => 9999,
                'renderMode' => 'tree',
                'treeConfig' => [
                    'parentField' => 'parent',
                    'appearance' => [
                        'expandAll' => false,
                        'showHeader' => true,
                    ],
                ],
                'wizards' => [
                    'add' => [
                        'type' => 'script',
                        'title' => $languageFile . 'sys_category.add',
                        'icon' => 'add.gif',
                        'params' => [
                            'table' => 'sys_category',
                            'pid' => '###CURRENT_PID###',
                            'setValue' => 'prepend'
                        ],
                        'module' => [
                            'name' => 'wizard_add'
                        ]
                    ]
                ],
            ]
        ],

        'attributes' => [
            'l10n_mode' => 'exclude',
            'exclude' => 1,
            'label' => $languageFile . 'tx_storefinder_domain_model_location.attributes',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_storefinder_domain_model_attribute',
                'foreign_table_where' => ' AND tx_storefinder_domain_model_attribute.sys_language_uid IN (-1,0)
                    AND tx_storefinder_domain_model_attribute.pid = ###CURRENT_PID###',
                'MM' => 'tx_storefinder_location_attribute_mm',
                'MM_match_fields' => [
                    'tablenames' => 'tx_storefinder_domain_model_attribute',
                    'fieldname' => 'attributes',
                ],
                'size' => 10,
                'maxitems' => 30,
            ]
        ],

        'icon' => [
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $languageFile . 'tx_storefinder_domain_model_location.icon',
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

        'latitude' => [
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $languageFile . 'tx_storefinder_domain_model_location.latitude',
            'config' => [
                'type' => 'input',
                'readOnly' => 1,
                'size' => 10,
            ]
        ],

        'longitude' => [
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $languageFile . 'tx_storefinder_domain_model_location.longitude',
            'config' => [
                'type' => 'input',
                'readOnly' => 1,
                'size' => 10,
            ]
        ],

        'center' => [
            'l10n_mode' => 'exclude',
            'exclude' => 1,
            'label' => $languageFile . 'tx_storefinder_domain_model_location.center',
            'config' => [
                'type' => 'check',
            ]
        ],

        'distance' => [
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $languageFile . 'tx_storefinder_domain_model_location.distance',
            'config' => [
                'type' => 'input',
                'readOnly' => 1,
                'size' => 10,
            ]
        ],
        'geocode' => [
            'l10n_mode' => 'exclude',
            'exclude' => 1,
            'label' => $languageFile . 'tx_storefinder_domain_model_location.geocode',
            'config' => [
                'type' => 'check',
            ]
        ],

        // informations
        'products' => [
            'exclude' => 0,
            'label' => $languageFile . 'tx_storefinder_domain_model_location.products',
            'config' => [
                'type' => 'input',
                'size' => '50',
                'eval' => 'trim',
                'max' => '255',
            ]
        ],

        'notes' => [
            'exclude' => 0,
            'label' => $languageFile . 'tx_storefinder_domain_model_location.notes',
            'config' => [
                'type' => 'text',
                'cols' => '80',
                'rows' => '15',
                'softref' => 'rtehtmlarea_images,typolink_tag,images,email[subst],url',
            ]
        ],

        'url' => [
            'exclude' => 0,
            'label' => $languageFile . 'tx_storefinder_domain_model_location.url',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'max' => '255',
                'eval' => 'trim',
                'wizards' => [
                    '_PADDING' => 2,
                    'link' => [
                        'type' => 'popup',
                        'title' => 'Link',
                        'icon' => 'link_popup.gif',
                        'module' => [
                            'name' => 'wizard_link',
                        ],
                        'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
                    ]
                ]
            ]
        ],

        'image' => [
            'exclude' => 0,
            'label' => $languageFile . 'tx_storefinder_domain_model_location.image',
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'image',
                [
                    'appearance' => [
                        'headerThumbnail' => [
                            'width' => '100',
                            'height' => '100c',
                        ],
                        'createNewRelationLinkTitle' => 'LLL:EXT:cms/locallang_ttc.xlf:images.addFileReference'
                    ],
                    // custom configuration for displaying fields in the overlay/reference table
                    // to use the imageoverlayPalette instead of the basicoverlayPalette
                    'foreign_types' => $foreignTypes,
                ],
                $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
            ),
        ],

        'media' => [
            'exclude' => 0,
            'label' => $languageFile . 'tx_storefinder_domain_model_location.media',
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'media',
                [
                    'appearance' => [
                        'headerThumbnail' => [
                            'width' => '100',
                            'height' => '100c',
                        ],
                        'createNewRelationLinkTitle' => 'LLL:EXT:cms/locallang_ttc.xlf:images.addFileReference'
                    ],
                    // custom configuration for displaying fields in the overlay/reference table
                    // to use the imageoverlayPalette instead of the basicoverlayPalette
                    'foreign_types' => $foreignTypes,
                ],
                $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
            ),
        ],

        'content' => [
            'exclude' => 1,
            'label' => $languageFile . 'tx_storefinder_domain_model_location.content',
            'config' => [
                'type' => 'inline',
                'allowed' => 'tt_content',
                'foreign_table' => 'tt_content',
                'minitems' => 0,
                'maxitems' => 10,
                'appearance' => [
                    'collapseAll' => 1,
                    'expandSingle' => 1,
                    'levelLinksPosition' => 'bottom',
                    'useSortable' => 1,
                    'showPossibleLocalizationRecords' => 1,
                    'showRemovedLocalizationRecords' => 1,
                    'showAllLocalizationLink' => 1,
                    'showSynchronizationLink' => 1,
                    'enabledControls' => [
                        'info' => false,
                    ]
                ]
            ]
        ],
    ],

    'types' => [
        '0' => [
            'showitem' => '
            --div--;' . $languageFile . 'div-address,
                name, storeid, address, additionaladdress, zipcode, city, state, country,
            --div--;' . $languageFile . 'div-contact,
                person, phone, mobile, fax, email, hours,
            --div--;' . $languageFile . 'div-relations,
                related, categories, attributes, icon,
                --palette--;' . $languageFile . 'palette-coordinates;coordinates,
            --div--;' . $languageFile . 'div-informations,
                products, notes;;;richtext:rte_transform[flag=rte_enabled|mode=ts_css], url,
                image;' . $languageFile . 'tx_storefinder_domain_model_location.image,
                media;' . $languageFile . 'tx_storefinder_domain_model_location.media, content,
            --div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access,
                --palette--;LLL:EXT:cms/locallang_ttc.xlf:palette.visibility;visibility,
                --palette--;LLL:EXT:cms/locallang_ttc.xlf:palette.access;access,'
        ]
    ],

    'palettes' => [
        'coordinates' => [
            'showitem' => 'latitude, longitude, center, geocode',
            'canNotCollapse' => 1
        ],
        'visibility' => [
            'showitem' => '
                hidden;' . $languageFile . 'tx_storefinder_domain_model_location.hidden',
            'canNotCollapse' => 1
        ],
        'access' => [
            'showitem' => '
				starttime;LLL:EXT:cms/locallang_ttc.xlf:starttime_formlabel,
				endtime;LLL:EXT:cms/locallang_ttc.xlf:endtime_formlabel,
				--linebreak--, fe_group;LLL:EXT:cms/locallang_ttc.xlf:fe_group_formlabel',
            'canNotCollapse' => 1
        ],
    ]
];
