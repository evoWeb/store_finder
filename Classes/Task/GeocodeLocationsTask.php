<?php
namespace Evoweb\StoreFinder\Task;

/***************************************************************
 * Copyright notice
 *
 * (c) 2011-13 Sebastian Fischer <typo3@evoweb.de>
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

/**
 * Class GeocodeLocationsTask
 *
 * @package Evoweb\StoreFinder\Task
 */
class GeocodeLocationsTask extends \TYPO3\CMS\Scheduler\Task\AbstractTask
{
    /**
     * @return bool
     */
    public function execute()
    {
        $globalConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['store_finder']);
        $globalConfiguration = is_array($globalConfiguration) ? $globalConfiguration : [];

        /**
         * @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager
         */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            'TYPO3\\CMS\\Extbase\\Object\\ObjectManager'
        );

        /**
         * @var \Evoweb\StoreFinder\Domain\Repository\LocationRepository $locationRepository
         */
        $locationRepository = $objectManager->get(\Evoweb\StoreFinder\Domain\Repository\LocationRepository::class);

        /**
         * @var \Evoweb\StoreFinder\Service\GeocodeService $geocodeService
         */
        $geocodeService = $objectManager->get(\Evoweb\StoreFinder\Service\GeocodeService::class, $globalConfiguration);

        /**
         * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager
         */
        $persistenceManager = $objectManager->get(\TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager::class);

        $loopCount = 0;
        $locationsToGeocode = $locationRepository->findAllWithoutLatLon();
        /** @var \Evoweb\StoreFinder\Domain\Model\Constraint $location */
        foreach ($locationsToGeocode as $location) {
            $location = $geocodeService->geocodeAddress($location);

            if ($location->getLatitude() && $location->getLongitude()) {
                $location->setGeocode(0);
            }

            $locationRepository->update($location);
            $loopCount++;

            if ($loopCount > 9) {
                $persistenceManager->persistAll();
                $loopCount = 0;
            }
        }

        if ($loopCount) {
            $persistenceManager->persistAll();
        }

        return true;
    }
}
