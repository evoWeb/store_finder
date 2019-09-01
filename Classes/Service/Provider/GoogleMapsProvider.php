<?php
declare(strict_types = 1);
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

        $apiConsoleKeyGeocoding = $settings['apiConsoleKeyGeocoding'];
        if (empty($apiConsoleKeyGeocoding)) {
            $apiConsoleKeyGeocoding = $settings['apiConsoleKey'];
        }

        $apiUrl = $settings['geocodeUrl'] .
            (!empty($apiConsoleKeyGeocoding) ? '&key=' . $apiConsoleKeyGeocoding : '') .
            '&address=' . implode('+', $parameter) .
            (!empty($components) ? '&components=' . implode('|', $components) : '');
        if (TYPO3_MODE == 'FE') {
            $apiUrl .= '&language=' . $this->getLanguageKey();
        }

        $addressData = json_decode(utf8_encode(
            \TYPO3\CMS\Core\Utility\GeneralUtility::getUrl(str_replace('?&', '?', $apiUrl))
        ));

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

    protected function getLanguageKey(): string
    {
        $controller = $this->getTypoScriptFrontendController();
        if (method_exists($controller, 'getLanguage')) {
            $languageKey = $controller->getLanguage()->getTwoLetterIsoCode();
        } else {
            // @todo remove once TYPO3 9.5.x support is dropped
            $languageKey = $controller->lang;
        }
        return $languageKey;
    }

    protected function getBeUser(): ?\TYPO3\CMS\Core\Authentication\BackendUserAuthentication
    {
        return isset($GLOBALS['BE_USER']) ? $GLOBALS['BE_USER'] : null;
    }

    protected function getTypoScriptFrontendController(): \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
