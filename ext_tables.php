<?php
defined('TYPO3_MODE') || die('Access denied.');


$GLOBALS['TYPO3_CONF_VARS']['SYS']['livesearch']['location'] = 'tx_storefinder_domain_model_location';


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_storefinder_domain_model_location');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToInsertRecords('tx_storefinder_domain_model_location');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_storefinder_domain_model_attribute');


$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
$iconRegistry->registerIcon(
    'store-finder-plugin',
    \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
    [
        'source' => 'EXT:store_finder/Resources/Public/Icons/google-maps-icon.png'
    ]
);
