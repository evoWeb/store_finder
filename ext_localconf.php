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

    /** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
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

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['livesearch']['location'] = 'tx_storefinder_domain_model_location';

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['store_finder'] =
        \Evoweb\StoreFinder\Hook\TceMainHook::class;

    // @deprecated and with be removed with support for TYPO3 8.7
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers']['GeocodeLocationsCommandController'] =
        \Evoweb\StoreFinder\Command\GeocodeLocationsCommandController::class;

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1549261866] = [
        'nodeName' => 'modifyLocationMap',
        'priority' => '70',
        'class' => \Evoweb\StoreFinder\Form\Element\ModifyLocationMap::class,
    ];
});
