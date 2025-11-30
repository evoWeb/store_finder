<?php

declare(strict_types=1);

/*
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Evoweb\StoreFinder\Form\FormDataGroup;

use TYPO3\CMS\Backend\Form\FormDataProvider\AbstractItemProvider;
use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Country\CountryProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A data provider group for casual database records
 */
class LocationCountryItems extends AbstractItemProvider implements FormDataProviderInterface
{
    /**
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    public function addData(array $result): array
    {
        $table = $result['tableName'];

        if (
            $table === 'tx_storefinder_domain_model_location'
            && isset($result['processedTca']['columns']['country'])
        ) {
            $fieldName = '';
            $items =& $result['processedTca']['columns']['country']['config']['items'];

            /** @var CountryProvider $countryProvider */
            $countryProvider = GeneralUtility::makeInstance(CountryProvider::class);
            foreach ($countryProvider->getAll() as $country) {
                $items[] = [
                    'label' => $country->getLocalizedNameLabel(),
                    'value' => $country->getAlpha2IsoCode(),
                ];
            }

            $items = $this->translateLabels(
                $result,
                $items,
                $table,
                $fieldName
            );
        }

        return $result;
    }
}
