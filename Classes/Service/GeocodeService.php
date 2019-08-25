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

use Evoweb\StoreFinder\Domain\Model\Constraint;
use Evoweb\StoreFinder\Domain\Model\Location;
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

    public function injectCoordinatesCache(\Evoweb\StoreFinder\Cache\CoordinatesCache $coordinatesCache)
    {
        $this->coordinatesCache = $coordinatesCache;
    }

    public function setSettings(array &$settings)
    {
        $this->settings = &$settings;

        $this->settings['geocodeLimit'] = (int) $this->settings['geocodeLimit'] ?? 2500;
        $this->settings['geocodeUrl'] = $this->settings['geocodeUrl'] ?? $this->defaultApiUrl;
    }

    /**
     * @param Location $address
     * @param bool $forceGeoCoding
     *
     * @return Constraint|Location
     */
    public function geocodeAddress(Location $address, bool $forceGeoCoding = false)
    {
        $geoCodedAddress = $this->coordinatesCache->getCoordinateByAddress($address);
        if ($forceGeoCoding || !$geoCodedAddress->isGeocoded()) {
            $fieldsHit = [];
            $geoCodedAddress = $this->processAddress($address, $fieldsHit);
            if (!$this->hasMultipleResults) {
                $this->coordinatesCache->addCoordinateForAddress($geoCodedAddress, $fieldsHit);
            }
        }

        // In case the address without geocoded location was stored in
        // session or the geocoding did not work a second try is done
        if (!$forceGeoCoding && !$geoCodedAddress->isGeocoded()) {
            $geoCodedAddress = $this->geocodeAddress($geoCodedAddress, true);
        }

        return $geoCodedAddress;
    }

    protected function processAddress(Location $location, array &$fields): Location
    {
        // Main geo coder
        $fields = ['address', 'zipcode', 'city', 'state', 'country'];
        $queryValues = $this->prepareValuesForQuery($location, $fields);
        $coordinate = $this->getCoordinateByApiCall($queryValues);

        // If there is no coordinate yet, we assume it's international and attempt
        // to find it based on just the city and country.
        if (!$coordinate->lat && !$coordinate->lng) {
            $fields = ['city', 'country'];
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

    protected function prepareValuesForQuery(Location $location, array &$fields): array
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

        $fields = array_keys($queryValues);

        return $queryValues;
    }

    protected function getCoordinateByApiCall(array $queryValues): \stdClass
    {
        if (strpos($this->settings['apiProvider'], '\\') === false) {
            $providerClass = 'Evoweb\\StoreFinder\\Service\\Provider\\'
                . ucfirst($this->settings['apiProvider']) . 'Provider';
        } else {
            $providerClass = $this->settings['apiProvider'];
        }

        $provider = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($providerClass);
        if ($provider instanceof \Evoweb\StoreFinder\Service\Provider\EncodeProviderInterface) {
            list($this->hasMultipleResults, $result) = $provider->encodeAddress($queryValues, $this->settings);
        } else {
            $result = new \stdClass();
        }

        return $result;
    }
}
