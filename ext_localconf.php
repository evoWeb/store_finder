<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['store_finder_coordinate'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['store_finder_coordinate'] = array(
        'groups' => array('system'),
    );
}

/**
 * Default PageTS
 */
/** @noinspection PhpUndefinedVariableInspection */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:' . $_EXTKEY . '/Configuration/PageTS/ModWizards.ts">'
);

$configuration = \Evoweb\StoreFinder\Utility\ExtensionConfigurationUtility::getConfiguration();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('
	options.saveDocNew.tx_storefinder_domain_model_location = 1
	options.saveDocNew.tx_storefinder_domain_model_category = 1
	options.saveDocNew.tx_storefinder_domain_model_attribute = 1
');

/** @noinspection PhpUndefinedVariableInspection */
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Evoweb.' . $_EXTKEY,
    'Map',
    array(
        'Map' => 'map',
    ),
    array(
        'Map' => 'map',
    )
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['store_finder'] =
    \Evoweb\StoreFinder\Hook\TceMainHook::class;

// Add location geocodeing task
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\Evoweb\StoreFinder\Task\GeocodeLocationsTask::class] =
    array(
        'extension' => $_EXTKEY,
        'title' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_be.xml:geocodeLocations.name',
        'description' => 'LLL:EXT:' . $_EXTKEY .
            '/Resources/Private/Language/locallang_be.xml:geocodeLocations.description',
    );
