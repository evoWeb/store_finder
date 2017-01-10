<?php
namespace Evoweb\StoreFinder\Hook;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Evoweb\StoreFinder\Domain\Model\Location;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * Class TceMainHook
 *
 * @package Evoweb\StoreFinder\Hook
 */
class TceMainHook
{
    /**
     * @var array
     */
    protected $configuration = array();

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected $objectManager = null;

    /**
     * @var \Evoweb\StoreFinder\Domain\Repository\LocationRepository
     */
    protected $repository = null;

    /**
     * After database operations hook
     *
     * @param string $status
     * @param string $table
     * @param string $id
     * @param array $fieldArray
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $parentObject
     *
     * @throws \InvalidArgumentException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @return void
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
     * @return integer
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
     * Initialization of configurations
     *
     * @return void
     */
    protected function initializeConfiguration()
    {
        $this->configuration = \Evoweb\StoreFinder\Utility\ExtensionConfigurationUtility::getConfiguration();
    }


    /**
     * Fetch location for uid
     *
     * @param integer $uid
     *
     * @throws \InvalidArgumentException
     * @return Location
     */
    protected function fetchLocation($uid)
    {
        /** @var Location $location */
        $location = $this->getRepository()->findByUid($uid);
        return $location;
    }

    /**
     * Getter for repository
     *
     * @throws \InvalidArgumentException
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     * @throws \TYPO3\CMS\Extbase\Object\Exception\CannotBuildObjectException
     * @return \Evoweb\StoreFinder\Domain\Repository\LocationRepository
     */
    protected function getRepository()
    {
        if ($this->repository === null) {
            $this->repository = $this->getObjectManager()
                ->get(\Evoweb\StoreFinder\Domain\Repository\LocationRepository::class);
        }

        return $this->repository;
    }

    /**
     * Getter for object manager
     *
     * @throws \InvalidArgumentException
     * @return \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected function getObjectManager()
    {
        if ($this->objectManager === null) {
            $this->objectManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        }

        return $this->objectManager;
    }


    /**
     * Sets coordinates by using geocoding service
     *
     * @param Location $location
     *
     * @throws \InvalidArgumentException
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     * @throws \TYPO3\CMS\Extbase\Object\Exception\CannotBuildObjectException
     * @return Location
     */
    protected function setCoordinates(Location $location)
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
     *
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \InvalidArgumentException
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     * @throws \TYPO3\CMS\Extbase\Object\Exception\CannotBuildObjectException
     * @return void
     */
    protected function storeLocation(Location $location)
    {
        $this->getRepository()->update($location);

        /** @var PersistenceManager $persistenceManager */
        $persistenceManager = $this->getObjectManager()
            ->get(PersistenceManager::class);
        $persistenceManager->persistAll();
    }
}
