<?php
namespace Evoweb\StoreFinder\Service;

/***************************************************************
 * Copyright notice
 *
 * (c) 2014 Sebastian Fischer <typo3@evoweb.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use Evoweb\StoreFinder\Domain\Model;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class GeocodeService
 *
 * @package Evoweb\StoreFinder\Service
 */
class GeocodeService
{
    /**
     * @var string
     */
    protected $defaultApiUrl = 'https://maps.googleapis.com/maps/api/geocode/json?';

    /**
     * @var array
     */
    protected $settings = array();

    /**
     * @var \Evoweb\StoreFinder\Cache\CoordinatesCache
     */
    protected $coordinatesCache;

    /**
     * @var bool
     */
    public $hasMultipleResults;

    /**
     * Constructor
     *
     * @param array $settings
     */
    public function __construct(array $settings = array())
    {
        if (count($settings)) {
            $this->setSettings($settings);
        } else {
            $this->settings['geocodeUrl'] = $this->defaultApiUrl;
            $this->settings['geocodeLimit'] = 2500;
        }
    }

    /**
     * @param \Evoweb\StoreFinder\Cache\CoordinatesCache $coordinatesCache
     */
    public function injectCoordinatesCache(\Evoweb\StoreFinder\Cache\CoordinatesCache $coordinatesCache)
    {
        $this->coordinatesCache = $coordinatesCache;
    }

    /**
     * Setter
     *
     * @param array &$settings
     *
     * @return void
     */
    public function setSettings(array &$settings)
    {
        $this->settings = &$settings;

        $this->settings['geocodeLimit'] = $this->settings['geocodeLimit'] ? (int) $this->settings['geocodeLimit'] :
            2500;
        $this->settings['geocodeUrl'] = $this->settings['geocodeUrl'] ? $this->settings['geocodeUrl'] :
            $this->defaultApiUrl;
    }

    /**
     * Geocode address and retries if first attempt or value in session
     * is not geocoded
     *
     * @param Model\Constraint|Model\Location $address
     * @param bool $forceGeocoding
     *
     * @return Model\Constraint|Model\Location
     */
    public function geocodeAddress($address, $forceGeocoding = false)
    {
        $geocodedAddress = $this->coordinatesCache->getCoordinateByAddress($address);
        if ($forceGeocoding || !$geocodedAddress->isGeocoded()) {
            $fieldsHit = array();
            $geocodedAddress = $this->processAddress($address, $fieldsHit);
            if (!$this->hasMultipleResults) {
                $this->coordinatesCache->addCoordinateForAddress($geocodedAddress, $fieldsHit);
            }
        }

        // In case the address without geocoded location was stored in
        // session or the geocoding did not work a second try is done
        if (!$forceGeocoding && !$geocodedAddress->isGeocoded()) {
            $geocodedAddress = $this->geocodeAddress($geocodedAddress, true);
        }

        return $geocodedAddress;
    }

    /**
     * Geocode address
     *
     * @param Model\Location|Model\Constraint $location
     * @param array &$fields
     *
     * @throws \RuntimeException
     * @throws \UnexpectedValueException
     * @return mixed
     */
    protected function processAddress($location, &$fields)
    {
        // Main Geocoder
        $fields = array('address', 'zipcode', 'city', 'state', 'country');
        $queryValues = $this->prepareValuesForQuery($location, $fields);
        $coordinate = $this->getCoordinateByApiCall($queryValues);

        // If there is no coordinat yet, we assume it's international and attempt
        // to find it based on just the city and country.
        if (!$coordinate->lat && !$coordinate->lng) {
            $fields = array('city', 'country');
            $queryValues = $this->prepareValuesForQuery($location, $fields);
            $coordinate = $this->getCoordinateByApiCall($queryValues);
        }

        // We should have coordinates by now and add them to location
        if ($coordinate->lat && $coordinate->lng) {
            $location->setLatitude($coordinate->lat);
            $location->setLongitude($coordinate->lng);
            $location->setGeocode(0);
        }

        return $location;
    }

    /**
     * Prepare query
     *
     * @param Model\Location|Model\Constraint $location
     * @param array $fields
     *
     * @throws \RuntimeException
     * @throws \UnexpectedValueException
     * @return array
     */
    protected function prepareValuesForQuery($location, &$fields)
    {
        // for url encoding
        $queryValues = array();
        foreach ($fields as $field) {
            $methodName = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));
            $value = $location->{$methodName}();

            switch ($field) {
                // if a known country code is used we fetch the english short name
                // to enhance the map api query result
                case 'country':
                    if (is_numeric($value) || strlen($value) == 3) {
                        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $database */
                        $database = $GLOBALS['TYPO3_DB'];
                        $country = $database->exec_SELECTgetSingleRow(
                            'cn_iso_2',
                            'static_countries',
                            (is_numeric($value) ? 'uid = ' : 'cn_iso_3 = ') .
                            $database->fullQuoteStr($value, 'static_countries')
                        );
                        if (count($country)) {
                            $value = reset($country);
                        }
                    } elseif (is_object($value) && method_exists($value, 'getIsoCodeA2')) {
                        /** @var \SJBR\StaticInfoTables\Domain\Model\Country $value */
                        $value = $value->getIsoCodeA2();
                    }
                    break;

                default:
            }

            if (!empty($value) && !is_object($value) && !is_array($value)) {
                $queryValues[$field] = urlencode($value);
            }
        }

        $fields = array_keys($queryValues);

        return $queryValues;
    }

    /**
     * Get coordinates by query google maps api
     *
     * @param array $parameter
     *
     * @return \stdClass
     */
    protected function getCoordinateByApiCall($parameter)
    {
        $components = array();
        if (isset($parameter['country'])) {
            $components[] = 'country:' . $parameter['country'];
            unset($parameter['country']);
        }
        if (isset($parameter['zipcode']) && count($parameter) > 1) {
            $components[] = 'postal_code:' . $parameter['zipcode'];
            unset($parameter['zipcode']);
        }


        $apiUrl = $this->settings['geocodeUrl'] . '&address=' . implode('+', $parameter);
        $apiUrl .= (!empty($components) ? '&components=' . implode('|', $components) : '');
        if (TYPO3_MODE == 'FE' && isset($this->getTypoScriptFrontendController()->lang)) {
            $apiUrl .= '&language=' . $this->getTypoScriptFrontendController()->lang;
        }
        $addressData = json_decode(utf8_encode(GeneralUtility::getUrl(str_replace('?&', '?', $apiUrl))));

        if (is_object($addressData) && property_exists($addressData, 'status') && $addressData->status === 'OK') {
            $this->hasMultipleResults = count($addressData->results) > 1;
            $result = $addressData->results[0]->geometry->location;
        } else {
            $result = new \stdClass();
        }

        return $result;
    }

    /**
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }
}
