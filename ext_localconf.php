<?php

defined('TYPO3_MODE') || die('Access denied.');

call_user_func(function () {
    if (
        !isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['store_finder_coordinate'])
        || !is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['store_finder_coordinate'])
    ) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['store_finder_coordinate'] = [
            'groups' => ['system'],
        ];
    }

    /** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $iconRegistry->registerIcon(
        'store-finder-plugin',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        [
            'source' => 'EXT:store_finder/Resources/Public/Icons/Extension.svg'
        ]
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        '@import \'EXT:store_finder/Configuration/TSconfig/NewContentElementWizard.typoscript\''
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('
        options.saveDocNew.tx_storefinder_domain_model_location = 1
        options.saveDocNew.tx_storefinder_domain_model_attribute = 1
    ');

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'StoreFinder',
        'Map',
        [\Evoweb\StoreFinder\Controller\MapController::class => 'map, search, show'],
        [\Evoweb\StoreFinder\Controller\MapController::class => 'map, search, show']
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'StoreFinder',
        'Show',
        [\Evoweb\StoreFinder\Controller\MapController::class => 'show'],
        [\Evoweb\StoreFinder\Controller\MapController::class => 'show']
    );

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['livesearch']['location'] = 'tx_storefinder_domain_model_location';

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['store_finder'] =
        \Evoweb\StoreFinder\EventListener\TceMainListener::class;

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1549261866] = [
        'nodeName' => 'modifyLocationMap',
        'priority' => '70',
        'class' => \Evoweb\StoreFinder\Form\Element\ModifyLocationMap::class,
    ];
});
