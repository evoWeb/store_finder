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
use Evoweb\StoreFinder\Domain\Model\Constraint;
use Evoweb\StoreFinder\Domain\Model\Location;
use Geocoder\Model\Coordinates;
use Geocoder\Provider\GoogleMaps\GoogleMaps;
use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;
use Geocoder\StatefulGeocoder;
use SJBR\StaticInfoTables\Domain\Model\Country;
use SJBR\StaticInfoTables\Domain\Model\CountryZone;
use TYPO3\CMS\Core\Http\Client\GuzzleClientFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class GeocodeService
{
    protected array $settings = [];

    protected array $fields = ['address', 'zipcode', 'city', 'state', 'country'];

    public bool $hasMultipleResults = false;

    public function __construct(
        protected CoordinatesCache $coordinatesCache,
        private readonly GuzzleClientFactory $guzzleFactory
    ) {
    }

    public function setSettings(array $settings): void
    {
        $this->settings = $settings;
    }

    public function geocodeAddress(Location|Constraint $address, bool $forceGeoCoding = false): Location|Constraint
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
            $methodName = 'get' . GeneralUtility::underscoredToUpperCamelCase($field);
            $value = $location->{$methodName}();

            switch ($field) {
                // if a known country code is used we fetch the english short name
                // to enhance the map api query result
                case 'country':
                    if ($value instanceof Country) {
                        $value = $value->getIsoCodeA2();
                    }
                    break;

                case 'state':
                    if ($value instanceof CountryZone) {
                        $value = $value->getLocalName();
                    }
                    break;

                default:
            }

            if (!empty($value) && !is_object($value) && !is_array($value)) {
                $queryValues[$field] =  \iconv('UTF-8', 'ASCII//TRANSLIT', $value);
            }
        }

        if (!isset($queryValues['country'])) {
            throw new \Exception(
                'Country may never be empty. Check your TypoScript setup to define a default constraint. Query: '
                . var_export($queryValues, true),
                1618235512
            );
        }

        return $queryValues;
    }

    protected function getCoordinatesFromProvider(array $queryValues): Coordinates
    {
        if (!str_contains($this->settings['geocoderProvider'], '\\')) {
            $providerClass = GoogleMaps::class;
        } else {
            $providerClass = $this->settings['geocoderProvider'];
        }

        $httpClient = $this->guzzleFactory->getClient();
        $provider = GeneralUtility::makeInstance(
            $providerClass,
            $httpClient,
            null,
            $this->settings['apiConsoleKeyGeocoding']
        );
        $result = null;
        if ($provider instanceof Provider) {
            $country = $queryValues['country'] ?? '';
            unset($queryValues['country']);

            $query = GeocodeQuery::create(implode(',', $queryValues));
            $query = $query->withData('components', 'country:' . $country);

            $geoCoder = new StatefulGeocoder($provider, $this->settings['geocoderLocale']);
            $results = $geoCoder->geocodeQuery($query);
            $this->hasMultipleResults = $results->count() > 1;
            if ($results->count() > 0) {
                $result = $results->get(0)->getCoordinates();
            }
        }

        if ($result === null) {
            $result = new Coordinates(0, 0);
        }

        return $result;
    }
}
