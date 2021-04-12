<?php

declare(strict_types=1);

namespace Evoweb\StoreFinder\Service;

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

use Evoweb\StoreFinder\Cache\CoordinatesCache;
use Evoweb\StoreFinder\Domain\Model\Location;
use Evoweb\StoreFinder\Domain\Repository\CountryRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class GeocodeService
{
    /**
     * @var CoordinatesCache
     */
    protected $coordinatesCache;

    /**
     * @var CountryRepository
     */
    protected $countryRepository;

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var array
     */
    protected $fields = ['address', 'zipcode', 'city', 'state', 'country'];

    /**
     * @var bool
     */
    public $hasMultipleResults;

    public function __construct(
        CoordinatesCache $coordinatesCache,
        CountryRepository $countryRepository
    ) {
        $this->coordinatesCache = $coordinatesCache;
        $this->countryRepository = $countryRepository;
    }

    public function setSettings(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @param Location $address
     * @param bool $forceGeoCoding
     *
     * @return Location
     */
    public function geocodeAddress(Location $address, bool $forceGeoCoding = false)
    {
        $queryValues = $this->prepareValuesForQuery($address, $this->fields);
        $geoCodedAddress = $this->coordinatesCache->getCoordinateByAddress($address, $queryValues);
        if ($forceGeoCoding || !$geoCodedAddress->isGeocoded()) {
            $geoCodedAddress = $this->processAddress($address, $queryValues);
            if (!$this->hasMultipleResults) {
                $this->coordinatesCache->addCoordinateForAddress($geoCodedAddress, $queryValues);
            }
        }

        // In case the address without geocoded location was stored in
        // session or the geocoding did not work a second try is done
        if (!$forceGeoCoding && !$geoCodedAddress->isGeocoded()) {
            $geoCodedAddress = $this->geocodeAddress($geoCodedAddress, true);
        }

        return $geoCodedAddress;
    }

    protected function processAddress(Location $location, array $queryValues): Location
    {
        if (empty($queryValues)) {
            return $location;
        }

        // Main geo coder
        $coordinate = $this->getCoordinatesFromProvider($queryValues);

        // If there is no coordinate yet, we assume it's international and attempt
        // to find it based on just the city and country.
        if (!$coordinate->getLatitude() && !$coordinate->getLongitude()) {
            $fields = ['city', 'country'];
            $queryValues = $this->prepareValuesForQuery($location, $fields);
            $coordinate = $this->getCoordinatesFromProvider($queryValues);
        }

        // We should have coordinates by now and add them to location
        if ($coordinate->getLatitude() && $coordinate->getLongitude()) {
            $location->setLatitude($coordinate->getLatitude());
            $location->setLongitude($coordinate->getLongitude());
            $location->setGeocode(0);
        }

        return $location;
    }

    public function prepareValuesForQuery(Location $location, array $fields): array
    {
        // for url encoding
        $queryValues = [];
        foreach ($fields as $field) {
            $methodName = 'get' . \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($field);
            $value = $location->{$methodName}();

            switch ($field) {
                // if a known country code is used we fetch the english short name
                // to enhance the map api query result
                case 'country':
                    if (is_numeric($value) || strlen((string) $value) == 3) {
                        if (is_numeric($value)) {
                            $value = $this->countryRepository->findByUid($value);
                        } else {
                            $value = $this->countryRepository->findByIsoCodeA3($value);
                        }
                    }

                    if ($value instanceof \SJBR\StaticInfoTables\Domain\Model\Country) {
                        $value = $value->getIsoCodeA2();
                    }
                    break;

                case 'state':
                    if ($value instanceof \SJBR\StaticInfoTables\Domain\Model\CountryZone) {
                        $value = $value->getLocalName();
                    }
                    break;

                default:
            }

            if (!empty($value) && !is_object($value) && !is_array($value)) {
                $queryValues[$field] =  iconv('UTF-8', 'ASCII//TRANSLIT', $value);
            }
        }

        if (!isset($queryValues['country'])) {
            throw new \Exception('Country may never be empty query: ' . var_export($queryValues, true), 1618235512);
        }

        return $queryValues;
    }

    protected function getCoordinatesFromProvider(array $queryValues): \Geocoder\Model\Coordinates
    {
        if (strpos($this->settings['geocoderProvider'], '\\') === false) {
            $providerClass = 'Geocoder\\Provider\\GoogleMaps\\GoogleMaps';
        } else {
            $providerClass = $this->settings['geocoderProvider'];
        }

        $httpClient = new \Http\Adapter\Guzzle6\Client();
        $provider = GeneralUtility::makeInstance(
            $providerClass,
            $httpClient,
            null,
            $this->settings['apiConsoleKeyGeocoding']
        );
        $result = null;
        if ($provider instanceof \Geocoder\Provider\Provider) {
            $country = $queryValues['country'] ?? '';
            unset($queryValues['country']);

            $query = \Geocoder\Query\GeocodeQuery::create(implode(',', $queryValues));
            $query = $query->withData('components', 'country:' . $country);

            $geoCoder = new \Geocoder\StatefulGeocoder($provider, $this->settings['geocoderLocale']);
            $results = $geoCoder->geocodeQuery($query);
            $this->hasMultipleResults = $results->count() > 1;
            if ($results->count() > 0) {
                $result = $results->get(0)->getCoordinates();
            }
        }

        if ($result === null) {
            $result = new \Geocoder\Model\Coordinates(0, 0);
        }

        return $result;
    }
}
