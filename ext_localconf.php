<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(function () {
    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['store_finder_coordinate'])
        || !is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['store_finder_coordinate'])
    ) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['store_finder_coordinate'] = [
            'groups' => ['system'],
        ];
    }

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['livesearch']['location'] = 'tx_storefinder_domain_model_location';

    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $iconRegistry->registerIcon(
        'store-finder-plugin',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        [
            'source' => 'EXT:store_finder/Resources/Public/Icons/Extension.svg'
        ]
    );

    /**
     * Default PageTSConfig
     */
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:'
        . 'store_finder/Configuration/PageTSconfig/NewContentElementWizard.typoscript">'
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('
        options.saveDocNew.tx_storefinder_domain_model_location = 1
        options.saveDocNew.tx_storefinder_domain_model_category = 1
        options.saveDocNew.tx_storefinder_domain_model_attribute = 1
    ');

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Evoweb.store_finder',
        'Map',
        ['Map' => 'map'],
        ['Map' => 'map']
    );

    $scOptions =& $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'];

    $scOptions['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['store_finder'] =
        \Evoweb\StoreFinder\Hook\TceMainHook::class;

    // Add location geocodeing task
    $scOptions['scheduler']['tasks'][\Evoweb\StoreFinder\Task\GeocodeLocationsTask::class] = [
        'extension' => 'store_finder',
        'title' => 'Switch to the extbase command controller task: "StoreFinder GeocodeLocations: geocode"',
        'description' => 'Switch to the extbase command controller task: "StoreFinder GeocodeLocations: geocode"',
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers']['GeocodeLocationsCommandController'] =
        \Evoweb\StoreFinder\Command\GeocodeLocationsCommandController::class;
});
