<?php
declare(strict_types = 1);
namespace Evoweb\StoreFinder\Cache;

/**
 * This file is developed by evoWeb.
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
    protected $fieldCombinations = [
        ['address', 'zipcode', 'city', 'state', 'country'],
        ['zipcode', 'city', 'country'],
        ['zipcode', 'country'],
        ['city', 'country'],
    ];

    public function __construct(\TYPO3\CMS\Core\Cache\Frontend\FrontendInterface $cacheFrontend = null)
    {
        if (!is_null($cacheFrontend)) {
            $this->cacheFrontend = $cacheFrontend;
        } else {
            $this->initializeCacheFrontend();
        }
    }

    public function injectFrontendUser(
        \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication $frontendUser
    ) {
        $this->frontendUser = $frontendUser;
    }

    protected function initializeCacheFrontend()
    {
        /** @var \TYPO3\CMS\Core\Cache\CacheManager $cacheManager */
        $cacheManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Cache\CacheManager::class
        );
        $this->cacheFrontend = $cacheManager->getCache('store_finder_coordinate');
    }

    /**
     * Add calculated coordinate for hash
     *
     * @param Model\Constraint|Model\Location $address
     * @param array $fields
     */
    public function addCoordinateForAddress($address, array $fields)
    {
        $coordinate = [
            'latitude' => $address->getLatitude(),
            'longitude' => $address->getLongitude()
        ];

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
     * @param Model\Constraint|Model\Location $address
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
    public function getHashForAddressWithFields($address, array &$fields): string
    {
        $values = [];

        foreach ($fields as $field) {
            $methodName = 'get' . GeneralUtility::underscoredToUpperCamelCase($field);
            if ($address !== null && method_exists($address, $methodName)) {
                $value = $address->{$methodName}();
                if ($value instanceof \SJBR\StaticInfoTables\Domain\Model\Country) {
                    $values[$field] = $value->getShortNameEn();
                } elseif ($value) {
                    $values[$field] = $value;
                }
            }
        }

        asort($values);
        $fields = array_keys($values);

        return md5(serialize(array_values($values)));
    }

    /**
     * Flush both sql table and session caches
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
    public function sessionHasKey(string $key): bool
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
    public function getValueFromSession(string $key): array
    {
        $sessionData = null;

        if ($this->frontendUser != null) {
            $sessionData = $this->frontendUser->getKey('ses', 'tx_storefinder_coordinates');
        }

        return is_array($sessionData) && isset($sessionData[$key]) ? unserialize($sessionData[$key]) : [];
    }

    /**
     * Store coordinate for hash in session
     *
     * @param string $key
     * @param array $value
     */
    public function setValueInSession(string $key, $value)
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
     */
    public function flushSessionCache()
    {
        if ($this->frontendUser != null) {
            $this->frontendUser->setKey('ses', 'tx_storefinder_coordinates', []);
            $this->frontendUser->storeSessionData();
        }
    }


    /**
     * Check if cache table has key set
     *
     * @param string $key
     *
     * @return bool
     */
    public function cacheTableHasKey(string $key): bool
    {
        return $this->cacheFrontend->has($key) && $this->getValueFromCacheTable($key) !== false;
    }

    /**
     * Fetch value for hash from session
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getValueFromCacheTable(string $key)
    {
        return $this->cacheFrontend->get($key);
    }

    /**
     * Store coordinate for hash in cache table
     *
     * @param string $key
     * @param array $value
     */
    public function setValueInCacheTable(string $key, array $value)
    {
        $this->cacheFrontend->set($key, $value);
    }

    /**
     * Flush data from cache table
     */
    public function flushCacheTable()
    {
        $this->cacheFrontend->flush();
    }
}
