<?php
declare(strict_types = 1);
namespace Evoweb\StoreFinder\Hook;

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

use Evoweb\StoreFinder\Domain\Repository\LocationRepository;
use Evoweb\StoreFinder\Service\CacheService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class TceMainHook
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
    }

    /**
     * Remap id for id and table
     *
     * @param string $id
     * @param string &$table
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $parentObject
     *
     * @return int
     */
    protected function remapId($id, &$table, $parentObject)
    {
        if (array_key_exists($id, $parentObject->substNEWwithIDs)) {
            $table = $parentObject->substNEWwithIDs_table[$id];
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
            $locationRepository = $this->getLocationRepository();
            $location = $locationRepository->findByUidInBackend($id);

            if ($location !== null && $location->getGeocode()) {
                $location = $this->getGeocodeService()->geocodeAddress($location);

                $locationRepository->update($location);

                /** @var PersistenceManager $persistenceManager */
                $persistenceManager = $this->objectManager->get(PersistenceManager::class);
                $persistenceManager->persistAll();
            }
        }

        // Clear caches if required
        switch ($table) {
            case 'tx_storefinder_domain_model_location':
                GeneralUtility::makeInstance(CacheService::class)
                    ->flushCacheByTag('tx_storefinder_domain_model_location_' . $id);
                break;
            case 'sys_category':
                GeneralUtility::makeInstance(CacheService::class)
                    ->flushCacheByTag('tx_storefinder_domain_model_category_' . $id);
                break;
            default:
        }
    }

    protected function getGeocodeService(): \Evoweb\StoreFinder\Service\GeocodeService
    {
        /** @var \Evoweb\StoreFinder\Service\GeocodeService $geocodeService */
        $geocodeService = $this->objectManager->get(
            \Evoweb\StoreFinder\Service\GeocodeService::class,
            \Evoweb\StoreFinder\Utility\ExtensionConfigurationUtility::getConfiguration()
        );
        return $geocodeService;
    }

    protected function getLocationRepository(): LocationRepository
    {
        /** @var LocationRepository $repository */
        $repository = $this->objectManager->get(LocationRepository::class);
        return $repository;
    }
}
