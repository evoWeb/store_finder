<?php

return [
    'store-finder-plugin' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:store_finder/Resources/Public/Icons/Extension.svg',
    ],
    'store-finder-attribute' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
        'source' => 'EXT:store_finder/Resources/Public/Icons/tx_storefinder_domain_model_attribute.gif',
    ],
    'store-finder-location' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
        'source' => 'EXT:store_finder/Resources/Public/Icons/tx_storefinder_domain_model_location.gif',
    ],
];
