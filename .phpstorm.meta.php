<?php

namespace PHPSTORM_META
{
    $serviceClassname = 'Evoweb\\StoreFinder\\Service\\GeocodeService';

    /** @noinspection PhpUnusedLocalVariableInspection */
    /** @noinspection PhpIllegalArrayKeyTypeInspection */
    /** @noinspection PhpDynamicAsStaticMethodCallInspection */
    $STATIC_METHOD_TYPES = [
        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('') => [
            $serviceClassname instanceof \Evoweb\StoreFinder\Service\GeocodeService,
        ],
        \TYPO3\CMS\Extbase\Object\ObjectManager::get('') => [
            $serviceClassname instanceof \Evoweb\StoreFinder\Service\GeocodeService,
        ],
    ];
}
