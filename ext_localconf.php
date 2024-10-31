<?php

declare(strict_types=1);

defined('TYPO3') or die();

use Evoweb\StoreFinder\Controller\MapController;
use Evoweb\StoreFinder\Form\Element\ModifyLocationMap;
use Evoweb\StoreFinder\Form\FormDataGroup\LocationCountryItems;
use Evoweb\StoreFinder\Hooks\TceMainListener;
use TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems as TcaSelectItems;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

(static function () {
    $cacheConfigurations = & $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'];
    if (!is_array($cacheConfigurations['store_finder_coordinate_cache'] ?? null)) {
        $cacheConfigurations['store_finder_coordinate_cache'] = [
            'groups' => [ 'system' ],
        ];
    }

    if (!is_array($cacheConfigurations['store_finder_middleware_cache'] ?? null)) {
        $cacheConfigurations['store_finder_middleware_cache'] = [
            'groups' => [ 'pages' ],
        ];
    }

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['livesearch']['location'] = 'tx_storefinder_domain_model_location';

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['store_finder'] =
        TceMainListener::class;

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1549261866] = [
        'nodeName' => 'modifyLocationMap',
        'priority' => '70',
        'class' => ModifyLocationMap::class,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][
        LocationCountryItems::class
    ] = [
        'depends' => [ TcaSelectItems::class ]
    ];

    ExtensionUtility::configurePlugin(
        'StoreFinder',
        'Map',
        [ MapController::class => 'map, search, show' ],
        [ MapController::class => 'map, search, show' ],
        ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    ExtensionUtility::configurePlugin(
        'StoreFinder',
        'Cached',
        [ MapController::class => 'cachedMap, map, search, show' ],
        [ MapController::class => 'map, search, show' ],
        ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );

    ExtensionUtility::configurePlugin(
        'StoreFinder',
        'Show',
        [ MapController::class => 'show' ],
        [ MapController::class => 'show' ],
        ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
    );
})();
