<?php
namespace Evoweb\StoreFinder\Service;

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

use Evoweb\StoreFinder\Domain\Model;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class GeocodeService
{
    /**
     * @var string
     */
    protected $defaultApiUrl = 'https://maps.googleapis.com/maps/api/geocode/json?';

    /**
     * @var array
     */
    protected $settings = [];

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
    public function __construct(array $settings = [])
    {
        if (count($settings)) {
            $this->setSettings($settings);
        } else {
            $this->settings['geocodeUrl'] = $this->defaultApiUrl;
            $this->settings['geocodeLimit'] = 2500;
        }
    }

    public function injectCoordinatesCache(
        \Evoweb\StoreFinder\Cache\CoordinatesCache $coordinatesCache
    ) {
        $this->coordinatesCache = $coordinatesCache;
    }

    /**
     * Setter
     *
     * @param array &$settings
     */
    public function setSettings(array &$settings)
    {
        $this->settings = &$settings;

        $this->settings['geocodeLimit'] = $this->settings['geocodeLimit'] ?
            (int) $this->settings['geocodeLimit'] :
            2500;
        $this->settings['geocodeUrl'] = $this->settings['geocodeUrl'] ?
            $this->settings['geocodeUrl'] :
            $this->defaultApiUrl;
    }

    /**
     * Geocode address and retries if first attempt or value in session is not geocoded
     *
     * @param Model\Constraint|Model\Location $address
     * @param bool $forceGeocoding
     *
     * @return Model\Constraint|Model\Location
     */
    public function geocodeAddress($address, bool $forceGeocoding = false)
    {
        $geocodedAddress = $this->coordinatesCache->getCoordinateByAddress($address);
        if ($forceGeocoding || !$geocodedAddress->isGeocoded()) {
            $fieldsHit = [];
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
     * @return Model\Location|Model\Constraint
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
     * @return array
     */
    protected function prepareValuesForQuery($location, &$fields): array
    {
        // for urlencoding
        $queryValues = [];
        foreach ($fields as $field) {
            $methodName = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));
            $value = $location->{$methodName}();

            switch ($field) {
                // if a known country code is used we fetch the english shortname
                // to enhance the map api query result
                case 'country':
                    if (is_numeric($value) || strlen($value) == 3) {
                        $queryBuilder = $this->getQueryBuilderForTable('static_countries');

                        if (is_numeric($value)) {
                            $constraint = $queryBuilder->expr()->eq(
                                'uid',
                                $queryBuilder->createNamedParameter($value, \PDO::PARAM_INT)
                            );
                        } else {
                            $constraint = $queryBuilder->expr()->eq(
                                'cn_iso_3',
                                $queryBuilder->createNamedParameter($value, \PDO::PARAM_STR)
                            );
                        }

                        $country = $queryBuilder
                            ->select('cn_iso_2')
                            ->from('static_countries')
                            ->where($constraint)
                            ->execute()
                            ->fetchColumn(0);

                        if (!empty($country)) {
                            $value = $country;
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
    protected function getCoordinateByApiCall(array $parameter): \stdClass
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

        $apiUrl = $this->settings['geocodeUrl'] .
            (!empty($this->settings['apiConsoleKey']) ? '&key=' . $this->settings['apiConsoleKey'] : '') .
            '&address=' . implode('+', $parameter) .
            (!empty($components) ? '&components=' . implode('|', $components) : '');
        if (TYPO3_MODE == 'FE' && isset($GLOBALS['TSFE']->lang)) {
            $apiUrl .= '&language=' . $GLOBALS['TSFE']->lang;
        }

        if ($this->settings['useConsoleKeyForGeocoding'] && !empty($this->settings['apiConsoleKey'])) {
            $apiUrl .= '&key=' . $this->settings['apiConsoleKey'];
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

    protected function getQueryBuilderForTable(string $table): \TYPO3\CMS\Core\Database\Query\QueryBuilder
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Database\ConnectionPool::class
        )->getQueryBuilderForTable($table);
    }
}
