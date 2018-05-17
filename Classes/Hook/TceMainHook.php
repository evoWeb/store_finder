<?php
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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Evoweb\StoreFinder\Domain\Model\Location;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class TceMainHook
{
    /**
     * @var array
     */
    protected $configuration = [];

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Evoweb\StoreFinder\Domain\Repository\LocationRepository
     */
    protected $repository;

    /**
     * After database operations hook
     *
     * @param string $status
     * @param string $table
     * @param string $id
     * @param array $fieldArray
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $parentObject
     */
    public function processDatamap_afterDatabaseOperations($status, $table, $id, $fieldArray, $parentObject)
    {
        if ($table === 'tx_storefinder_domain_model_location') {
            $locationId = $this->remapId($id, $table, $parentObject);

            $this->initializeConfiguration();
            $location = $this->fetchLocation($locationId);

            if ($location !== null && $location->getGeocode()) {
                $this->storeLocation($this->setCoordinates($location));
            }
        }
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

    protected function initializeConfiguration()
    {
        $this->configuration = \Evoweb\StoreFinder\Utility\ExtensionConfigurationUtility::getConfiguration();
    }


    protected function fetchLocation(int $uid): Location
    {
        /** @var Location $location */
        $location = $this->getRepository()->findByUid($uid);
        return $location;
    }

    protected function getRepository(): \Evoweb\StoreFinder\Domain\Repository\LocationRepository
    {
        if ($this->repository === null) {
            $this->repository = $this->getObjectManager()
                ->get(\Evoweb\StoreFinder\Domain\Repository\LocationRepository::class);
        }

        return $this->repository;
    }

    protected function getObjectManager(): \TYPO3\CMS\Extbase\Object\ObjectManager
    {
        if ($this->objectManager === null) {
            $this->objectManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        }

        return $this->objectManager;
    }


    /**
     * Sets coordinates by using geo coding service
     *
     * @param Location $location
     *
     * @return Location
     */
    protected function setCoordinates(Location $location): Location
    {
        /** @var \Evoweb\StoreFinder\Service\GeocodeService $geocodeService */
        $geocodeService = $this->getObjectManager()
            ->get(\Evoweb\StoreFinder\Service\GeocodeService::class, $this->configuration);
        $location = $geocodeService->geocodeAddress($location);

        return $location;
    }

    /**
     * Stores location
     *
     * @param Location $location
     */
    protected function storeLocation(Location $location)
    {
        $this->getRepository()->update($location);

        /** @var PersistenceManager $persistenceManager */
        $persistenceManager = $this->getObjectManager()
            ->get(\TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager::class);
        $persistenceManager->persistAll();
    }
}
