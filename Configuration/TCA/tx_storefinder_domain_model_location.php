<?php

$languageFile = 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xlf:';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToInsertRecords(
    'tx_storefinder_domain_model_location'
);

return [
    'ctrl' => [
        'label' => 'name',
        'sortby' => 'sorting',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'title' => $languageFile . 'tx_storefinder_domain_model_location',
        'transOrigPointerField' => 'l18n_parent',
        'transOrigDiffSourceField' => 'l18n_diffsource',
        'languageField' => 'sys_language_uid',
        'translationSource' => 'l10n_source',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
            'fe_group' => 'fe_group',
        ],
        'searchFields' => 'name, storeid, zipcode, city, address, country, notes',
        'typeicon_classes' => [
            'default' => 'store-finder-attribute',
        ],
    ],

    'columns' => [
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:pages.hidden_toggle',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        'label' => '',
                        'invertStateDisplay' => true,
                    ],
                ],
            ],
        ],
        'starttime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'datetime',
                'default' => 0,
            ],
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
        ],
        'endtime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'datetime',
                'default' => 0,
                'range' => [
                    'upper' => mktime(0, 0, 0, 1, 1, 2038),
                ],
            ],
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
        ],
        'fe_group' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.fe_group',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'size' => 5,
                'maxitems' => 20,
                'items' => [
                    [
                        'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hide_at_login',
                        'value' => -1,
                    ],
                    [
                        'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.any_login',
                        'value' => -2,
                    ],
                    [
                        'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.usergroups',
                        'value' => '--div--',
                    ],
                ],
                'exclusiveKeys' => '-1,-2',
                'foreign_table' => 'fe_groups',
            ],
        ],
        'sys_language_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'language',
            ],
        ],
        'l18n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => '', 'value' => 0],
                ],
                'foreign_table' => 'tt_content',
                'foreign_table_where' =>
                    'AND tx_storefinder_domain_model_location.pid = ###CURRENT_PID###
                     AND tx_storefinder_domain_model_location.sys_language_uid = 0',
                'default' => 0,
            ],
        ],
        'l10n_source' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'l18n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
                'default' => '',
            ],
        ],

        // address
        'name' => [
            'label' => $languageFile . 'tx_storefinder_domain_model_location.name',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'max' => 255,
                'eval' => 'trim',
                'required' => true
            ],
        ],

        'storeid' => [
            'l10n_mode' => 'exclude',
            'label' => $languageFile . 'tx_storefinder_domain_model_location.storeid',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],

        'address' => [
            'label' => $languageFile . 'tx_storefinder_domain_model_location.address',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 3,
                'eval' => 'trim',
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],

        'additionaladdress' => [
            'label' => $languageFile . 'tx_storefinder_domain_model_location.additionaladdress',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],

        'zipcode' => [
            'l10n_mode' => 'exclude',
            'label' => $languageFile . 'tx_storefinder_domain_model_location.zipcode',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'required' => true
            ],
        ],

        'city' => [
            'label' => $languageFile . 'tx_storefinder_domain_model_location.city',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'required' => true,
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],

        'state' => [
            'label' => $languageFile . 'tx_storefinder_domain_model_location.state',
            'displayCond' => 'FIELD:country:>:0',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => '', 'value' => 0],
                ],
                'foreign_table' => 'static_country_zones',
                'foreign_table_where' => 'AND zn_country_uid = ###REC_FIELD_country###
                    ORDER BY static_country_zones.zn_name_local',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],

        'country' => [
            'label' => $languageFile . 'tx_storefinder_domain_model_location.country',
            'onChange' => 'reload',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => '', 'value' => 0],
                ],
                'foreign_table' => 'static_countries',
                'itemsProcFunc' =>
                    \SJBR\StaticInfoTables\Hook\Backend\Form\FormDataProvider\TcaSelectItemsProcessor::class .
                    '->translateCountriesSelector',
                'size' => 1,
                'minitems' => 1,
                'maxitems' => 1,
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],

        // contact
        'person' => [
            'label' => $languageFile . 'tx_storefinder_domain_model_location.person',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],

        'phone' => [
            'label' => $languageFile . 'tx_storefinder_domain_model_location.phone',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],

        'mobile' => [
            'label' => $languageFile . 'tx_storefinder_domain_model_location.mobile',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],

        'fax' => [
            'label' => $languageFile . 'tx_storefinder_domain_model_location.fax',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],

        'email' => [
            'label' => $languageFile . 'tx_storefinder_domain_model_location.email',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],

        'hours' => [
            'label' => $languageFile . 'tx_storefinder_domain_model_location.hours',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],

        // relations
        'related' => [
            'l10n_mode' => 'exclude',
            'label' => $languageFile . 'tx_storefinder_domain_model_location.related',
            'config' => [
                'type' => 'group',
                'allowed' => 'tx_storefinder_domain_model_location',
                'foreign_table' => 'tx_storefinder_domain_model_location',
                'foreign_table_where' => 'AND tx_storefinder_domain_model_location.uid != ###THIS_UID###
                    ORDER BY tx_storefinder_domain_model_location.name',
                'MM' => 'tx_storefinder_location_location_mm',
                'MM_match_fields' => [
                    'tablenames' => 'tx_storefinder_domain_model_location',
                    'fieldname' => 'related',
                ],
            ],
        ],

        'categories' => [
            'l10n_mode' => 'exclude',
            'label' => $languageFile . 'tx_storefinder_domain_model_location.categories',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectTree',
                'foreign_table' => 'sys_category',
                'foreign_table_where' => 'AND sys_category.sys_language_uid IN (-1,0) ORDER BY sys_category.title ASC',
                'MM' => 'sys_category_record_mm',
                'MM_opposite_field' => 'items',
                'MM_match_fields' => [
                    'tablenames' => 'tx_storefinder_domain_model_location',
                    'fieldname' => 'categories',
                ],
                'size' => 10,
                'maxitems' => 9999,
                'treeConfig' => [
                    'parentField' => 'parent',
                    'appearance' => [
                        'expandAll' => false,
                        'showHeader' => true,
                    ],
                ],
                'fieldControl' => [
                    'addRecord' => [
                        'options' => [
                            'pid' => '###CURRENT_PID###',
                            'setValue' => 'prepend',
                            'table' => 'sys_category',
                            'title' => $languageFile . 'sys_category.add',
                        ],
                    ],
                ],
            ],
        ],

        'attributes' => [
            'l10n_mode' => 'exclude',
            'exclude' => true,
            'label' => $languageFile . 'tx_storefinder_domain_model_location.attributes',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_storefinder_domain_model_attribute',
                'foreign_table_where' =>
                    'AND tx_storefinder_domain_model_attribute.sys_language_uid IN (-1,0) AND '
                    . 'tx_storefinder_domain_model_attribute.pid = ###CURRENT_PID###',
                'MM' => 'tx_storefinder_location_attribute_mm',
                'MM_match_fields' => [
                    'tablenames' => 'tx_storefinder_domain_model_attribute',
                    'fieldname' => 'attributes',
                ],
                'size' => 10,
                'maxitems' => 30,
            ],
        ],

        'latitude' => [
            'l10n_mode' => 'exclude',
            'label' => $languageFile . 'tx_storefinder_domain_model_location.latitude',
            'config' => [
                'type' => 'input',
                'size' => 10,
                'default' => 0,
            ],
        ],

        'longitude' => [
            'l10n_mode' => 'exclude',
            'label' => $languageFile . 'tx_storefinder_domain_model_location.longitude',
            'config' => [
                'type' => 'input',
                'size' => 10,
                'default' => 0,
            ],
        ],

        'center' => [
            'l10n_mode' => 'exclude',
            'label' => $languageFile . 'tx_storefinder_domain_model_location.center',
            'config' => [
                'type' => 'check',
            ],
        ],

        'distance' => [
            'l10n_mode' => 'exclude',
            'label' => $languageFile . 'tx_storefinder_domain_model_location.distance',
            'config' => [
                'type' => 'input',
                'readOnly' => 1,
                'size' => 10,
            ],
        ],
        'geocode' => [
            'l10n_mode' => 'exclude',
            'exclude' => true,
            'label' => $languageFile . 'tx_storefinder_domain_model_location.geocode',
            'config' => [
                'type' => 'check',
            ],
        ],
        'map' => [
            'l10n_mode' => 'exclude',
            'exclude' => true,
            'label' => $languageFile . 'tx_storefinder_domain_model_location.modifyLocationMap',
            'config' => [
                'type' => 'check',
                'renderType' => 'modifyLocationMap',
            ],
        ],

        // information
        'products' => [
            'label' => $languageFile . 'tx_storefinder_domain_model_location.products',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'eval' => 'trim',
                'max' => 255,
            ],
        ],

        'notes' => [
            'label' => $languageFile . 'tx_storefinder_domain_model_location.notes',
            'config' => [
                'type' => 'text',
                'cols' => 80,
                'rows' => 15,
                'enableRichtext' => true,
                'softref' => 'rtehtmlarea_images,typolink_tag,images,email[subst],url',
            ],
        ],

        'url' => [
            'label' => $languageFile . 'tx_storefinder_domain_model_location.url',
            'config' => [
                'type' => 'link',
                'eval' => 'trim',
                'fieldControl' => ['linkPopup' => ['options' => ['title' => 'Link']]],
                'size' => 30,
                'max' => 255,
            ],
        ],

        'icon' => [
            'l10n_mode' => 'exclude',
            'label' => $languageFile . 'tx_storefinder_domain_model_location.icon',
            'config' => [
                'type' => 'file',
                'allowed' => 'common-image-types',
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],

        'image' => [
            'label' => $languageFile . 'tx_storefinder_domain_model_location.image',
            'config' =>[
                'type' => 'file',
                'allowed' => 'common-image-types',
            ],
        ],

        'media' => [
            'label' => $languageFile . 'tx_storefinder_domain_model_location.media',
            'config' => [
                'type' => 'file',
                'allowed' => 'common-media-types',
            ]
        ],

        'layer' => [
            'l10n_mode' => 'exclude',
            'label' => $languageFile . 'tx_storefinder_domain_model_location.layer',
            'config' => [
                'type' => 'file',
                'minitems' => 0,
                'maxitems' => 1,
                'allowed' => [ 'svg', 'kml' ]
            ],
        ],

        'content_elements' => [
            'exclude' => true,
            'label' => $languageFile . 'tx_storefinder_domain_model_location.content_elements',
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
                    'showAllLocalizationLink' => 1,
                    'showSynchronizationLink' => 1,
                    'enabledControls' => [
                        'info' => false,
                    ],
                ],
            ],
        ],
    ],

    'types' => [
        '0' => [
            'showitem' => '
                --div--;' . $languageFile . 'div-address,
                    --palette--;;name,
                    --palette--;;address,
                    --palette--;;coordinates,
                --div--;' . $languageFile . 'div-contact,
                    person,
                    --palette--;;contact,
                    url,
                    hours,
                --div--;' . $languageFile . 'div-informations,
                    products,
                    notes,
                    icon,
                    image,
                    media,
                    layer,
                    content,
                --div--;' . $languageFile . 'div-relations,
                    related,
                    attributes,
                    categories,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                    --palette--;;hidden,
                    --palette--;;access,
            ',
        ],
    ],

    'palettes' => [
        'name' => [
            'label' => $languageFile . 'palette-name',
            'showitem' => '
                name, storeid
            ',
        ],
        'address' => [
            'label' => $languageFile . 'palette-address',
            'showitem' => '
                address, additionaladdress,
                --linebreak--,
                zipcode, city,
                --linebreak--,
                state, country
            ',
        ],
        'contact' => [
            'label' => $languageFile . 'palette-contact',
            'showitem' => '
                phone, mobile,
                --linebreak--,
                fax, email
            ',
        ],
        'coordinates' => [
            'label' => $languageFile . 'palette-coordinates',
            'showitem' => '
                map,
                --linebreak--,
                latitude, longitude,
                --linebreak--,
                geocode, center
            ',
        ],
        'hidden' => [
            'showitem' => '
                hidden;' . $languageFile . 'tx_storefinder_domain_model_location.hidden
            ',
        ],
        'access' => [
            'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access',
            'showitem' => '
                starttime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel,
                endtime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel,
                --linebreak--,
                fe_group;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:fe_group_formlabel,
            ',
        ],
    ],
];
