<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

(static function () {
    $languageFile = 'LLL:EXT:store_finder/Resources/Private/Language/locallang_be.xlf:';
    $GLOBALS['TCA']['tt_content']['palettes']['storefinder-frames'] = [
        'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.frames',
        'showitem' => '
            frame_class;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:frame_class_formlabel,
            space_before_class;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:space_before_class_formlabel,
            space_after_class;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:space_after_class_formlabel
        '
    ];

    $showItems = '
            --palette--;;general,
            --palette--;;headers,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:plugin,
            pi_flexform,
            pages;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:pages.ALT.list_formlabel,
            recursive,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
            --palette--;;storefinder-frames,
            --palette--;;appearanceLinks,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
            categories,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
            --palette--;;language,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
            --palette--;;hidden,
            --palette--;;access,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
            rowDescription,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended
    ';

    ExtensionUtility::registerPlugin(
        'store_finder',
        'Map',
        $languageFile . 'tt_content.list_type_map'
    );
    $GLOBALS['TCA']['tt_content']['types']['storefinder_map']['showitem'] = $showItems;

    ExtensionManagementUtility::addPiFlexFormValue(
        '*',
        'FILE:EXT:store_finder/Configuration/FlexForms/flexform_mapWithSearch.xml',
        'storefinder_map'
    );

    ExtensionUtility::registerPlugin(
        'store_finder',
        'Cached',
        $languageFile . 'tt_content.list_type_cached'
    );
    $GLOBALS['TCA']['tt_content']['types']['storefinder_cached']['showitem'] = $showItems;

    ExtensionManagementUtility::addPiFlexFormValue(
        '*',
        'FILE:EXT:store_finder/Configuration/FlexForms/flexform_mapWithSearch.xml',
        'storefinder_cached'
    );

    ExtensionUtility::registerPlugin(
        'store_finder',
        'Show',
        $languageFile . 'tt_content.list_type_show'
    );
    $GLOBALS['TCA']['tt_content']['types']['storefinder_show']['showitem'] = $showItems;

    ExtensionManagementUtility::addPiFlexFormValue(
        '*',
        'FILE:EXT:store_finder/Configuration/FlexForms/flexform_singleLocation.xml',
        'storefinder_show'
    );
})();
