<?php

$languageFile = 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xlf:';

return [
    'ctrl' => [
        'label' => 'name',
        'label_alt' => 'icon',
        'label_alt_force' => '1',
        'sortby' => 'sorting',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'title' => $languageFile . 'tx_storefinder_domain_model_attribute',
        'transOrigPointerField' => 'l18n_parent',
        'transOrigDiffSourceField' => 'l18n_diffsource',
        'languageField' => 'sys_language_uid',
        'translationSource' => 'l10n_source',
        'selicon_field' => 'icon',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'typeicon_classes' => [
            'default' => 'store-finder-attribute',
        ],
    ],

    'columns' => [
        'name' => [
            'label' => $languageFile . 'tx_storefinder_domain_model_attribute.name',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'max' => 255,
                'eval' => 'trim',
                'required' => true,
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],

        'icon' => [
            'label' => $languageFile . 'tx_storefinder_domain_model_attribute.icon',
            'config' => [
                'type' => 'file',
                'allowed' => 'common-image-types',
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],

        'description' => [
            'label' => $languageFile . 'tx_storefinder_domain_model_attribute.description',
            'config' => [
                'type' => 'text',
                'cols' => 80,
                'rows' => 15,
                'enableRichtext' => true,
                'softref' => 'typolink_tag,images,email[subst],url',
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],

        'css_class' => [
            'label' => $languageFile . 'tx_storefinder_domain_model_attribute.css_class',
            'config' => [
                'type' => 'input',
                'size' => 50,
                'max' => 255,
                'eval' => 'trim',
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        'import_id' => [
            'config' => [
                'type' => 'number'
            ]
        ],
    ],

    'types' => [
        '0' => [
            'showitem' => '
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    name, icon, description, css_class,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                    --palette--;;language,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                    --palette--;;hidden,
            ',
        ],
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
    ],
];
