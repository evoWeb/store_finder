<?php
namespace Evoweb\StoreFinder\Cache;

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

/**
 * Class CoordinatesCache
 *
 * @package Evoweb\StoreFinder\Domain\Repository
 */
class CoordinatesCache
{
    /**
     * @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication
     */
    protected $frontendUser;

    /**
     * @var \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface
     */
    protected $cacheFrontend;

    /**
     * @var array
     */
    protected $fieldCombinations = array(
        array('address', 'zipcode', 'city', 'state', 'country'),
        array('zipcode', 'city', 'country'),
        array('zipcode', 'country'),
        array('city', 'country'),
    );


    /**
     * Constructor
     *
     * @throws \TYPO3\CMS\Core\Cache\Exception\InvalidBackendException
     * @throws \TYPO3\CMS\Core\Cache\Exception\InvalidCacheException
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     */
    public function __construct()
    {
        /** @var \TYPO3\CMS\Core\Cache\CacheManager $cacheManager */
        $cacheManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Cache\CacheManager::class);
        $this->injectCacheFrontend($cacheManager->getCache('store_finder_coordinate'));
    }

    /**
     * @param \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication $frontendUser
     */
    public function injectFrontendUser(\TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication $frontendUser)
    {
        $this->frontendUser = $frontendUser;
    }

    /**
     * @param \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface $cacheFrontend
     */
    public function injectCacheFrontend(\TYPO3\CMS\Core\Cache\Frontend\FrontendInterface $cacheFrontend)
    {
        $this->cacheFrontend = $cacheFrontend;
    }


    /**
     * Add calculated coordinate for hash
     *
     * @param Model\Constraint|Model\Location $address
     * @param array $fields
     *
     * @throws \TYPO3\CMS\Core\Exception
     * @return void
     */
    public function addCoordinateForAddress($address, $fields)
    {
        $coordinate = array(
            'latitude' => $address->getLatitude(),
            'longitude' => $address->getLongitude()
        );

        $hash = $this->getHashForAddressWithFields($address, $fields);
        if (count($fields) == 2 || count($fields) == 3) {
            $this->setValueInCacheTable($hash, $coordinate);
        } elseif (count($fields) > 3) {
            $this->setValueInSession($hash, $coordinate);
        }
    }

    /**
     * Get coordinate by address
     *
     * @param Model\Constraint $address
     *
     * @return Model\Constraint|Model\Location
     */
    public function getCoordinateByAddress($address)
    {
        $coordinate = null;

        foreach ($this->fieldCombinations as $fields) {
            $hash = $this->getHashForAddressWithFields($address, $fields);

            if ($hash) {
                if (count($fields) <= 3) {
                    $coordinate = $this->getValueFromCacheTable($hash);
                } elseif ($this->sessionHasKey($hash)) {
                    $coordinate = $this->getValueFromSession($hash);
                }

                if (is_array($coordinate)) {
                    $address->setLatitude($coordinate['latitude']);
                    $address->setLongitude($coordinate['longitude']);
                    break;
                }
            }
        }

        return $address;
    }

    /**
     * Get hash for address with field values
     *
     * @param Model\Constraint|Model\Location $address
     * @param array &$fields
     *
     * @return string
     */
    public function getHashForAddressWithFields($address, &$fields)
    {
        $values = array();

        foreach ($fields as $field) {
            $methodName = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));
            $value = $address->{$methodName}();

            if ($value) {
                $values[$field] = $value;
            }
        }

        asort($values);
        $fields = array_keys($values);

        return md5(serialize(array_values($values)));
    }

    /**
     * Flush both sql table and session caches
     *
     * @return void
     */
    public function flushCache()
    {
        $this->flushCacheTable();
        $this->flushSessionCache();
    }


    /**
     * Check if session has key set and the value is not empty
     *
     * @param string $key
     *
     * @return bool
     */
    public function sessionHasKey($key)
    {
        $sessionData = null;

        if ($this->frontendUser != null) {
            $sessionData = $this->frontendUser->getKey('ses', 'tx_storefinder_coordinates');
        }

        return is_array($sessionData) && isset($sessionData[$key]) && !empty($sessionData[$key]);
    }

    /**
     * Fetch value for hash from session
     *
     * @param string $key
     *
     * @return array
     */
    public function getValueFromSession($key)
    {
        $sessionData = null;

        if ($this->frontendUser != null) {
            $sessionData = $this->frontendUser->getKey('ses', 'tx_storefinder_coordinates');
        }

        return is_array($sessionData) && isset($sessionData[$key]) ? unserialize($sessionData[$key]) : null;
    }

    /**
     * Store coordinate for hash in session
     *
     * @param string $key
     * @param array $value
     *
     * @throws \TYPO3\CMS\Core\Exception
     * @return void
     */
    public function setValueInSession($key, $value)
    {
        if ($this->frontendUser != null) {
            $sessionData = $this->frontendUser->getKey('ses', 'tx_storefinder_coordinates');

            $sessionData[$key] = serialize($value);

            $this->frontendUser->setKey('ses', 'tx_storefinder_coordinates', $sessionData);
            $this->frontendUser->storeSessionData();
        }
    }

    /**
     * Flush session cache
     *
     * @throws \TYPO3\CMS\Core\Exception
     * @return void
     */
    public function flushSessionCache()
    {
        $this->frontendUser->setKey('ses', 'tx_storefinder_coordinates', array());
        $this->frontendUser->storeSessionData();
    }


    /**
     * Check if cache table has key set
     *
     * @param string $key
     *
     * @return bool
     */
    public function cacheTableHasKey($key)
    {
        return $this->cacheFrontend->has($key) && $this->getValueFromCacheTable($key) !== false;
    }

    /**
     * Fetch value for hash from session
     *
     * @param string $key
     *
     * @return array
     */
    public function getValueFromCacheTable($key)
    {
        return $this->cacheFrontend->get($key);
    }

    /**
     * Store coordinate for hash in cache table
     *
     * @param string $key
     * @param array $value
     *
     * @return void
     */
    public function setValueInCacheTable($key, $value)
    {
        $this->cacheFrontend->set($key, $value);
    }

    /**
     * Flush data from cache table
     *
     * @return void
     */
    public function flushCacheTable()
    {
        $this->cacheFrontend->flush();
    }
}
