<?php

declare(strict_types=1);

namespace Evoweb\StoreFinder\Hooks;

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
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class TceMainListener
{
    public function __construct(
        protected LocationRepository $locationRepository,
        protected PersistenceManager $persistenceManager,
        protected GeocodeService $geocodeService,
        protected CacheService $cacheService,
        ExtensionConfiguration $extensionConfiguration
    ) {
        try {
            $this->geocodeService->setSettings($extensionConfiguration->get('store_finder') ?? []);
        } catch (\Exception $e) {
            die('Error in $GLOBALS[\'TYPO3_CONF_VARS\'][\'EXTENSIONS\']: ' . $e->getMessage());
        }
    }

    /**
     * After database operations hook
     */
    public function processDatamap_afterDatabaseOperations(
        string $status,
        string $table,
        string|int $id,
        array $fieldValues,
        DataHandler $parentObject
    ): void {
        [$id, $table] = $this->remapId($id, $table, $parentObject);

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

    /**
     * Remap id for id and table
     */
    protected function remapId(string|int $NEW_id, string $table, DataHandler $parentObject): array
    {
        if (array_key_exists($NEW_id, $parentObject->substNEWwithIDs)) {
            $id = $parentObject->substNEWwithIDs[$NEW_id];
            $table = $parentObject->substNEWwithIDs_table[$NEW_id];
        } else {
            $id = $NEW_id;
        }

        return [(int)$id, $table];
    }
}
