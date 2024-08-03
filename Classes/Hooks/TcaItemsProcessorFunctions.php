<?php

declare(strict_types=1);

/*
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Evoweb\StoreFinder\Hooks;

use TYPO3\CMS\Core\Country\CountryProvider;

class TcaItemsProcessorFunctions
{
    public function __construct(protected CountryProvider $countryProvider)
    {
    }

    public function populateCountryItems(array &$fieldDefinition): void
    {
        foreach ($this->countryProvider->getAll() as $country) {
            $fieldDefinition['items'][] = [
                'label' => $country->getLocalizedNameLabel(),
                'value' => $country->getAlpha2IsoCode(),
            ];
        }
    }
}