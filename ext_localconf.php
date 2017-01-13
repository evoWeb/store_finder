<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(function () {
    if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['store_finder_coordinate'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['store_finder_coordinate'] = [
            'groups' => ['system'],
        ];
    }

    /**
     * Default PageTS
     */
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:store_finder/Configuration/PageTSconfig/NewContentElementWizard.ts">'
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('
        options.saveDocNew.tx_storefinder_domain_model_location = 1
        options.saveDocNew.tx_storefinder_domain_model_category = 1
        options.saveDocNew.tx_storefinder_domain_model_attribute = 1
    ');

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Evoweb.store_finder',
        'Map',
        [
            'Map' => 'map',
        ],
        [
            'Map' => 'map',
        ]
    );

    $scOptions =& $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'];
    $languageFile = 'LLL:EXT:store_finder/Resources/Private/Language/locallang_be.xml:';

    $scOptions['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['store_finder'] =
        \Evoweb\StoreFinder\Hook\TceMainHook::class;

    // Add location geocodeing task
    $scOptions['scheduler']['tasks'][\Evoweb\StoreFinder\Task\GeocodeLocationsTask::class] = [
        'extension' => 'store_finder',
        'title' => $languageFile . 'geocodeLocations.name',
        'description' => $languageFile . 'geocodeLocations.description',
    ];
});
