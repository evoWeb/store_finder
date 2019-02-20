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
 * https://developer.mapquest.com/documentation/geocoding-api/
 */
class MapQuestProvider implements EncodeProviderInterface
{
    public function encodeAddress(array $parameter, array $settings): array
    {
        $apiConsoleKeyGeocoding = $settings['apiConsoleKeyGeocoding'];
        if (empty($apiConsoleKeyGeocoding)) {
            $apiConsoleKeyGeocoding = $settings['apiConsoleKey'];
        }

        $apiUrl = $settings['geocodeUrl'] .
            (!empty($apiConsoleKeyGeocoding) ? '&key=' . $apiConsoleKeyGeocoding : '') .
            '&location=' . implode(',', $parameter);

        $addressData = json_decode(utf8_encode(
            \TYPO3\CMS\Core\Utility\GeneralUtility::getUrl(str_replace('?&', '?', $apiUrl))
        ));

        $hasMultipleResults = false;
        $result = new \stdClass();
        if (is_object($addressData) && property_exists($addressData, 'results') && count($addressData->results)) {
            $hasMultipleResults = count($addressData->results[0]->locations) > 1;
            foreach ($addressData->results[0]->locations as $location) {
                if ($location->adminArea1 === $parameter['country']) {
                    $result = $location->latLng;
                    break;
                }
            }
        }

        return [$hasMultipleResults, $result];
    }

    protected function getTypoScriptFrontendController(): \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
