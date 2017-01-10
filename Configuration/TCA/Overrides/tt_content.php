<?php

$pluginSignature = 'storefinder_map';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout, select_key';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    $pluginSignature,
    'FILE:EXT:store_finder/Configuration/FlexForms/flexform_mapWithSearch.xml'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'store_finder',
    'Map',
    'LLL:EXT:store_finder/Resources/Private/Language/locallang_be.xml:tt_content.list_type_map'
);

$pluginSignature = 'storefinder_show';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout, select_key';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    $pluginSignature,
    'FILE:EXT:store_finder/Configuration/FlexForms/flexform_singleLocation.xml'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'store_finder',
    'Show',
    'LLL:EXT:store_finder/Resources/Private/Language/locallang_be.xml:tt_content.list_type_show'
);
