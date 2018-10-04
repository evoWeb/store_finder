<?php
namespace Evoweb\StoreFinder\Service\Provider;

/**
 * This file is developed by evoweb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

/**
 * https://wiki.openstreetmap.org/wiki/Nominatim
 */
class NominatimProvider implements EncodeProviderInterface
{
    public function encodeAddress(array $parameter, array $settings): array
    {
        $components = [];

        if (isset($parameter['country']) && !empty($parameter['country'])) {
            $components[] = 'country=' . $parameter['country'];
        }
        if (isset($parameter['state']) && !empty($parameter['state'])) {
            $components[] = 'state=' . $parameter['state'];
        }
        if (isset($parameter['zipcode']) && !empty($parameter['zipcode'])) {
            $components[] = 'postalcode=' . $parameter['zipcode'];
        }
        if (isset($parameter['city']) && !empty($parameter['city'])) {
            $components[] = 'city=' . $parameter['city'];
        }
        if (isset($parameter['address']) && !empty($parameter['address'])) {
            $components[] = 'street=' . $parameter['address'];
        }

        $apiUrl = $settings['geocodeUrl'] . '&' . implode('&', $components);

        $addressData = json_decode(utf8_encode(
            \TYPO3\CMS\Core\Utility\GeneralUtility::getUrl(str_replace('?&', '?', $apiUrl))
        ));

        $hasMultipleResults = false;
        $result = new \stdClass();
        if (is_array($addressData)) {
            $hasMultipleResults = count($addressData) > 1;
            $result->lat = $addressData[0]->lat;
            $result->lng = $addressData[0]->lon;
        }

        return [$hasMultipleResults, $result];
    }

    protected function getTypoScriptFrontendController(): \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
