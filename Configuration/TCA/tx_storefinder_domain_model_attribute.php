<?php

$languageFile = 'LLL:EXT:store_finder/Resources/Private/Language/locallang_db.xml:';
$coreLangFile = 'LLL:EXT:lang/locallang_tca.xlf:';

return array(
    'ctrl' => array(
        'title' => $languageFile . 'tx_storefinder_domain_model_attribute',
        'label' => 'name',
        'label_alt' => 'icon',
        'label_alt_force' => '1',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'sortby' => 'sorting',
        'delete' => 'deleted',

        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l18n_parent',
        'transOrigDiffSourceField' => 'l18n_diffsource',

        'selicon_field' => 'icon',
        'selicon_field_path' => 'uploads/tx_storefinder',

        'enablecolumns' => array(
            'disabled' => 'hidden',
        ),
        'iconfile' => '../typo3conf/ext/store_finder/Resources/Public/Icons/tx_storefinder_domain_model_attribute.gif',
    ),

    'interface' => array(
        'showRecordFieldList' => 'sys_language_uid,l18n_parent,l18n_diffsource,name,icon'
    ),

    'columns' => array(
        'sys_language_uid' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
            'config' => array(
                'type' => 'select',
                'foreign_table' => 'sys_language',
                'foreign_table_where' => 'ORDER BY sys_language.title',
                'items' => array(
                    array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
                    array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
                )
            )
        ),
        'l18n_parent' => array(
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
            'config' => array(
                'type' => 'select',
                'items' => array(
                    array('', 0),
                ),
                'foreign_table' => 'tx_storefinder_domain_model_attribute',
                'foreign_table_where' => 'AND tx_storefinder_domain_model_attribute.pid=###CURRENT_PID###
                    AND tx_storefinder_domain_model_attribute.sys_language_uid IN (-1,0)',
            )
        ),
        'l18n_diffsource' => array(
            'config' => array(
                'type' => 'passthrough'
            )
        ),
        'name' => array(
            'exclude' => 0,
            'label' => $languageFile . 'tx_storefinder_domain_model_attribute.name',
            'config' => array(
                'type' => 'input',
                'size' => '30',
                'eval' => 'required,trim',
            )
        ),

        'icon' => array(
            'exclude' => 0,
            'label' => $languageFile . 'tx_storefinder_domain_model_attribute.icon',
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig('icon', array(
                'appearance' => array(
                    'createNewRelationLinkTitle' => 'LLL:EXT:cms/locallang_ttc.xlf:images.addFileReference'
                ),
                'minitems' => 0,
                'maxitems' => 1,
                // custom configuration for displaying fields in the overlay/reference table
                // to use the imageoverlayPalette instead of the basicoverlayPalette
                'foreign_types' => array(
                    '0' => array(
                        'showitem' => '
							--palette--;' . $coreLangFile . 'sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
                    ),
                    \TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => array(
                        'showitem' => '
							--palette--;' . $coreLangFile . 'sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
                    ),
                    \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => array(
                        'showitem' => '
							--palette--;' . $coreLangFile . 'sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
                    ),
                    \TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => array(
                        'showitem' => '
							--palette--;' . $coreLangFile . 'sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
                    ),
                    \TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => array(
                        'showitem' => '
							--palette--;' . $coreLangFile . 'sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
                    ),
                    \TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => array(
                        'showitem' => '
							--palette--;' . $coreLangFile . 'sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
                    )
                )
            ), $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']),
        ),
    ),

    'types' => array(
        '0' => array('showitem' => 'sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, name, icon')
    ),

    'palettes' => array(
        '1' => array('showitem' => '')
    )
);
