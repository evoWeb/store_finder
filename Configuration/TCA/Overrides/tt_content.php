<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

call_user_func(static function () {
    ExtensionManagementUtility::addTcaSelectItemGroup(
        'tt_content',
        'CType',
        'store_finder',
        'store_finder.db:tt_content.list_type_group'
    );

    ExtensionManagementUtility::addToInsertRecords('tx_storefinder_domain_model_location');

    $GLOBALS['TCA']['tt_content']['palettes']['storefinder-frames'] = [
        'label' => 'frontend.ttc:palette.frames',
        'showitem' => '
            frame_class;frontend.ttc:frame_class_formlabel,
            space_before_class;frontend.ttc:space_before_class_formlabel,
            space_after_class;frontend.ttc:space_after_class_formlabel
        ',
    ];

    $showItems = '
            --palette--;;general,
            --palette--;;headers,
        --div--;core.tabs:plugin,
            pi_flexform,
            pages;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:pages.ALT.list_formlabel,
            recursive,
        --div--;core.tabs:appearance,
            --palette--;;storefinder-frames,
            --palette--;;appearanceLinks,
        --div--;core.tabs:categories,
            categories,
    ';

    $pluginSignature = ExtensionUtility::registerPlugin(
        'store_finder',
        'Cached',
        'store_finder.db:tt_content.list_type_cached',
        'store-finder-plugin',
        'store_finder',
        'store_finder.db:tt_content.list_type_cached_description',
        'FILE:EXT:store_finder/Configuration/FlexForms/flexform_mapWithSearch.xml',
    );
    $GLOBALS['TCA']['tt_content']['types'][$pluginSignature]['showitem'] = $showItems;

    $pluginSignature = ExtensionUtility::registerPlugin(
        'store_finder',
        'Map',
        'store_finder.db:tt_content.list_type_map',
        'store-finder-plugin',
        'store_finder',
        'store_finder.db:tt_content.list_type_map_description',
        'FILE:EXT:store_finder/Configuration/FlexForms/flexform_mapWithSearch.xml',
    );
    $GLOBALS['TCA']['tt_content']['types'][$pluginSignature]['showitem'] = $showItems;

    $pluginSignature = ExtensionUtility::registerPlugin(
        'store_finder',
        'Show',
        'store_finder.db:tt_content.list_type_show',
        'store-finder-plugin',
        'store_finder',
        'store_finder.db:tt_content.list_type_show_description',
        'FILE:EXT:store_finder/Configuration/FlexForms/flexform_singleLocation.xml',
    );
    $GLOBALS['TCA']['tt_content']['types'][$pluginSignature]['showitem'] = $showItems;
});
