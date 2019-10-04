<?php
declare(strict_types = 1);
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

use Evoweb\StoreFinder\Domain\Model\Location;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class GeocodeService
{
    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var array
     */
    protected $fields = ['address', 'zipcode', 'city', 'state', 'country'];

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
        $this->setSettings($settings);
    }

    public function injectCoordinatesCache(\Evoweb\StoreFinder\Cache\CoordinatesCache $coordinatesCache)
    {
        $this->coordinatesCache = $coordinatesCache;
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

    protected function prepareValuesForQuery(Location $location, array $fields): array
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
                    if (is_numeric($value) || strlen((string) $value) == 3) {
                        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
                        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                            \TYPO3\CMS\Extbase\Object\ObjectManager::class
                        );
                        /** @var \Evoweb\StoreFinder\Domain\Repository\CountryRepository $repository */
                        $repository = $objectManager->get(
                            \Evoweb\StoreFinder\Domain\Repository\CountryRepository::class
                        );

                        if (is_numeric($value)) {
                            $value = $repository->findByUid($value);
                        } else {
                            $value = $repository->findByIsoCodeA3($value);
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
                $queryValues[$field] = urlencode($value);
            }
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
        $provider = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            $providerClass,
            $httpClient,
            null,
            $this->settings['apiConsoleKeyGeocoding']
        );
        if ($provider instanceof \Geocoder\Provider\Provider) {
            $geoCoder = new \Geocoder\StatefulGeocoder($provider, 'en');
            $results = $geoCoder->geocodeQuery(\Geocoder\Query\GeocodeQuery::create(implode(',', $queryValues)));
            $this->hasMultipleResults = $results->count() > 1;
            $result = $results->get(0)->getCoordinates();
        } else {
            $result = new \Geocoder\Model\Coordinates(0, 0);
        }

        return $result;
    }
}
