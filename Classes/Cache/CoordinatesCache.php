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
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\SetCookieService;
use TYPO3\CMS\Core\Session\UserSession;
use TYPO3\CMS\Core\Session\UserSessionManager;

class CoordinatesCache
{
    protected array $fields = ['address', 'zipcode', 'city', 'state', 'country'];

    protected string $sessionName = 'evoweb-storefinder-session';

    protected string $sessionKey = 'coordinates';

    protected UserSessionManager $userSessionManager;

    protected UserSession $session;

    public function __construct(protected FrontendInterface $cacheFrontend)
    {
    }

    public function initializeUserSessionManager(?UserSessionManager $userSessionManager = null): void
    {
        $this->userSessionManager = $userSessionManager ?? UserSessionManager::create('FE');
        $this->session = $userSessionManager->createFromRequestOrAnonymous(
            $this->getRequest(),
            $this->sessionName,
        );
    }

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
        return !empty($this->session->getData()[$this->sessionKey][$key] ?? []);
    }

    public function getValueFromSession(string $key): array
    {
        $sessionData = $this->session->get($this->sessionKey);

        return is_array($sessionData) && isset($sessionData[$key]) ? unserialize($sessionData[$key]) : [];
    }

    public function setValueInSession(string $key, array $value): void
    {
        $sessionData = $this->session->get($this->sessionKey);
        $sessionData[$key] = serialize($value);

        $this->session->set($this->sessionKey, $sessionData);
        $this->userSessionManager->updateSession($this->session);
        $setCookieService = SetCookieService::create($this->sessionName, 'FE');
        $normalizedParams = NormalizedParams::createFromRequest($this->getRequest());
        $setCookieService->setSessionCookie($this->session, $normalizedParams);
    }

    public function flushSessionCache(): void
    {
        $this->userSessionManager->removeSession($this->session);
        $setCookieService = SetCookieService::create($this->sessionName, 'FE');
        $normalizedParams = NormalizedParams::createFromRequest($this->getRequest());
        $setCookieService->removeCookie($normalizedParams);
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

    public function getRequest(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }
}
