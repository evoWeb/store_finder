<?php

declare(strict_types=1);

namespace Evoweb\StoreFinder\EventListener;

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

use Evoweb\StoreFinder\Domain\Repository\LocationRepository;
use Evoweb\StoreFinder\Service\CacheService;
use Evoweb\StoreFinder\Service\GeocodeService;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class TceMainListener
{
    /**
     * @var LocationRepository
     */
    protected $locationRepository;

    /**
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @var GeocodeService
     */
    protected $geocodeService;

    /**
     * @var CacheService
     */
    protected $cacheService;

    public function __construct(
        LocationRepository $locationRepository,
        PersistenceManager $persistenceManager,
        GeocodeService $geocodeService,
        ExtensionConfiguration $extensionConfiguration,
        CacheService $cacheService
    ) {
        $this->locationRepository = $locationRepository;
        $this->persistenceManager = $persistenceManager;
        $this->cacheService = $cacheService;
        $this->geocodeService = $geocodeService;
        $this->geocodeService->setSettings($extensionConfiguration->get('store_finder') ?? []);
    }

    /**
     * Remap id for id and table
     *
     * @param string $id
     * @param string $table
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $parentObject
     *
     * @return int
     */
    protected function remapId($id, $table, $parentObject)
    {
        if (array_key_exists($id, $parentObject->substNEWwithIDs)) {
            $id = $parentObject->substNEWwithIDs[$id];
        }

        return $id;
    }

    /**
     * After database operations hook
     *
     * @param string $_1
     * @param string $table
     * @param string $id
     * @param array $fieldArray
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $parentObject
     */
    public function processDatamap_afterDatabaseOperations($_1, $table, $id, $fieldArray, $parentObject)
    {
        $id = $this->remapId($id, $table, $parentObject);

        if ($table === 'tx_storefinder_domain_model_location') {
            $location = $this->locationRepository->findByUidInBackend($id);

            if ($location !== null && $location->getGeocode()) {
                $location = $this->geocodeService->geocodeAddress($location);

                $this->locationRepository->update($location);
                $this->persistenceManager->persistAll();
            }
        }

        // Clear caches if required
        switch ($table) {
            case 'tx_storefinder_domain_model_location':
                $this->cacheService->flushCacheByTag('tx_storefinder_domain_model_location_' . $id);
                break;
            case 'sys_category':
                $this->cacheService->flushCacheByTag('tx_storefinder_domain_model_category_' . $id);
                break;
            default:
        }
    }
}
