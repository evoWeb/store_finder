<?php

defined('TYPO3') or die();

use Evoweb\StoreFinder\Controller\MapController;
use Evoweb\StoreFinder\EventListener\TceMainListener;
use Evoweb\StoreFinder\Form\Element\ModifyLocationMap;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

call_user_func(function () {
    $cacheConfigurations =& $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'];
    if (!is_array($cacheConfigurations['store_finder_coordinate'] ?? null)) {
        $cacheConfigurations['store_finder_coordinate'] = [
            'groups' => ['system'],
        ];
    }

    if (!is_array($cacheConfigurations['store_finder_middleware_cache'] ?? null)) {
        $cacheConfigurations['store_finder_middleware_cache'] = [
            'groups' => ['pages'],
        ];
    }

    ExtensionManagementUtility::addPageTSConfig('
        @import \'EXT:store_finder/Configuration/TSconfig/NewContentElementWizard.tsconfig\'
        @import \'EXT:store_finder/Configuration/TSconfig/Plugin.tsconfig\'
    ');

    ExtensionManagementUtility::addUserTSConfig('
        options.saveDocNew.tx_storefinder_domain_model_location = 1
        options.saveDocNew.tx_storefinder_domain_model_attribute = 1
    ');

    ExtensionUtility::configurePlugin(
        'StoreFinder',
        'Map',
        [MapController::class => 'map, search, show'],
        [MapController::class => 'map, search, show']
    );

    ExtensionUtility::configurePlugin(
        'StoreFinder',
        'Cached',
        [MapController::class => 'cachedMap, map, search, show'],
        [MapController::class => 'map, search, show']
    );

    ExtensionUtility::configurePlugin(
        'StoreFinder',
        'Show',
        [MapController::class => 'show'],
        [MapController::class => 'show']
    );

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['livesearch']['location'] = 'tx_storefinder_domain_model_location';

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['store_finder'] =
        TceMainListener::class;

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1549261866] = [
        'nodeName' => 'modifyLocationMap',
        'priority' => '70',
        'class' => ModifyLocationMap::class,
    ];
});
