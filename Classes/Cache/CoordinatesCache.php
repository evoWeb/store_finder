<?php

declare(strict_types=1);

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

namespace Evoweb\StoreFinder\Cache;

use Evoweb\StoreFinder\Domain\Model\Location;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

class CoordinatesCache
{
    protected array $fields = ['address', 'zipcode', 'city', 'state', 'country'];

    protected string $sessionKey = 'tx_storefinder_coordinates';

    public function __construct(
        protected FrontendInterface $cacheFrontend,
        protected ?FrontendUserAuthentication $frontendUser = null
    ) {}

    public function addCoordinateForAddress(Location $address, array $queryValues): void
    {
        if (empty($queryValues)) {
            return;
        }

        $fields = array_keys($queryValues);
        $hash = md5(serialize(array_values($queryValues)));
        $coordinate = [
            'latitude' => $address->getLatitude(),
            'longitude' => $address->getLongitude(),
        ];

        if (count($fields) <= 3) {
            $this->setValueInCacheTable($hash, $coordinate);
        } else {
            $this->setValueInSession($hash, $coordinate);
        }
    }

    public function getCoordinateByAddress(Location $address, array $queryValues): Location
    {
        if (empty($queryValues)) {
            return $address;
        }

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

        return $address;
    }

    /**
     * Flush both sql table and session caches
     */
    public function flushCache(): void
    {
        $this->flushCacheTable();
        $this->flushSessionCache();
    }

    /**
     * Check if session has key set and return true if the value is not empty
     */
    public function sessionHasKey(string $key): bool
    {
        $sessionData = $this->frontendUser?->getKey('ses', $this->sessionKey);

        return is_array($sessionData) && !empty($sessionData[$key]);
    }

    public function getValueFromSession(string $key): array
    {
        $sessionData = $this->frontendUser?->getKey('ses', $this->sessionKey);

        return is_array($sessionData) && isset($sessionData[$key]) ? unserialize($sessionData[$key]) : [];
    }

    public function setValueInSession(string $key, array $value): void
    {
        if ($this->frontendUser instanceof FrontendUserAuthentication) {
            $sessionData = $this->frontendUser->getKey('ses', $this->sessionKey);

            $sessionData[$key] = serialize($value);

            $this->frontendUser->setKey('ses', $this->sessionKey, $sessionData);
            // @extensionScannerIgnoreLine
            $this->frontendUser->storeSessionData();
        }
    }

    public function flushSessionCache(): void
    {
        if ($this->frontendUser instanceof FrontendUserAuthentication) {
            $this->frontendUser->setKey('ses', $this->sessionKey, []);
            // @extensionScannerIgnoreLine
            $this->frontendUser->storeSessionData();
        }
    }

    /**
     * Check if cache table has key set
     */
    public function cacheTableHasKey(string $key): bool
    {
        return $this->cacheFrontend->has($key) && $this->getValueFromCacheTable($key) !== false;
    }

    /**
     * Fetch value for hash from session
     */
    public function getValueFromCacheTable(string $key): mixed
    {
        return $this->cacheFrontend->get($key);
    }

    /**
     * Store coordinate for hash in cache table
     */
    public function setValueInCacheTable(string $key, array $value): void
    {
        $this->cacheFrontend->set($key, $value);
    }

    /**
     * Flush data from cache table
     */
    public function flushCacheTable(): void
    {
        $this->cacheFrontend->flush();
    }
}
