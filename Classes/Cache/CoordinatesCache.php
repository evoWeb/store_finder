<?php

declare(strict_types=1);

namespace Evoweb\StoreFinder\Cache;

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

use Evoweb\StoreFinder\Domain\Model;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

class CoordinatesCache
{
    /**
     * @var FrontendUserAuthentication
     */
    protected $frontendUser;

    /**
     * @var FrontendInterface
     */
    protected $cacheFrontend;

    /**
     * @var array
     */
    protected $fields = ['address', 'zipcode', 'city', 'state', 'country'];

    public function __construct(
        FrontendInterface $cacheFrontend,
        FrontendUserAuthentication $frontendUser = null
    ) {
        $this->cacheFrontend = $cacheFrontend;
        $this->frontendUser = $frontendUser;
    }

    public static function getInstance(): ?self
    {
        /** @var CacheManager $cacheManager */
        $cacheManager = GeneralUtility::getContainer()->get(CacheManager::class);
        $cacheFrontend = $cacheManager->getCache('store_finder_coordinate');

        /** @var FrontendUserAuthentication $frontendUser */
        $frontendUser = ($GLOBALS['TSFE'] ?? null) ? $GLOBALS['TSFE']->fe_user : null;

        /** @var self $instance */
        $instance = GeneralUtility::makeInstance(self::class, $cacheFrontend, $frontendUser);
        return $instance;
    }

    /**
     * Add calculated coordinate for hash
     *
     * @param Model\Location $address
     * @param array $queryValues
     */
    public function addCoordinateForAddress($address, array $queryValues)
    {
        $coordinate = [
            'latitude' => $address->getLatitude(),
            'longitude' => $address->getLongitude()
        ];

        $fields = array_keys($queryValues);
        $hash = md5(serialize(array_values($queryValues)));
        if (count($fields) == 2 || count($fields) == 3) {
            $this->setValueInCacheTable($hash, $coordinate);
        } elseif (count($fields) > 3) {
            $this->setValueInSession($hash, $coordinate);
        }
    }

    /**
     * Get coordinate by address
     *
     * @param Model\Location $address
     * @param array $queryValues
     *
     * @return Model\Location
     */
    public function getCoordinateByAddress($address, array $queryValues)
    {
        if ($queryValues) {
            $fields = array_keys($queryValues);
            $hash = md5(serialize(array_values($queryValues)));

            $coordinate = null;
            if (count($fields) <= 3) {
                $coordinate = $this->getValueFromCacheTable($hash);
            } elseif ($this->sessionHasKey($hash)) {
                $coordinate = $this->getValueFromSession($hash);
            }

            if (is_array($coordinate)) {
                $address->setLatitude($coordinate['latitude']);
                $address->setLongitude($coordinate['longitude']);
            }
        }
        return $address;
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
