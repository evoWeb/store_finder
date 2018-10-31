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
 * https://developers.google.com/maps/documentation/geocoding/intro
 */
class GoogleMapsProvider implements EncodeProviderInterface
{
    public function encodeAddress(array $parameter, array $settings): array
    {
        $components = [];
        if (isset($parameter['country'])) {
            $components[] = 'country:' . $parameter['country'];
            unset($parameter['country']);
        }
        if (isset($parameter['zipcode']) && count($parameter) > 1) {
            $components[] = 'postal_code:' . $parameter['zipcode'];
            unset($parameter['zipcode']);
        }

        // nothing to encode so leave early
        if (empty($parameter) && empty($components)) {
            return [false, new \stdClass()];
        }

        $apiUrl = $settings['geocodeUrl'] .
            (!empty($settings['apiConsoleKey']) ? '&key=' . $settings['apiConsoleKey'] : '') .
            '&address=' . implode('+', $parameter) .
            (!empty($components) ? '&components=' . implode('|', $components) : '');
        if (TYPO3_MODE == 'FE' && isset($this->getTypoScriptFrontendController()->lang)) {
            $apiUrl .= '&language=' . $this->getTypoScriptFrontendController()->lang;
        }

        $addressData = json_decode(utf8_encode(
            \TYPO3\CMS\Core\Utility\GeneralUtility::getUrl(str_replace('?&', '?', $apiUrl)))
        );

        $hasMultipleResults = false;
        $result = new \stdClass();
        if (is_object($addressData) && property_exists($addressData, 'status') && $addressData->status === 'OK') {
            $hasMultipleResults = count($addressData->results) > 1;
            $result = $addressData->results[0]->geometry->location;
        } elseif ($this->getBeUser() !== null && is_object($addressData) && $addressData->error_message) {
            $this->getBeUser()->writelog(
                4,
                0,
                1,
                0,
                'store_finder - Class : ' . self::class . ' ' . $addressData->error_message,
                [$parameter, $components]
            );
        }

        return [$hasMultipleResults, $result];
    }

    /**
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected function getBeUser()
    {
        return isset($GLOBALS['BE_USER']) ? $GLOBALS['BE_USER'] : null;
    }

    protected function getTypoScriptFrontendController(): \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
