<?php
namespace Evoweb\StoreFinder\Command;

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

/**
 * Class GeocodeLocationsCommandController
 *
 * @deprecated and with be removed with support for TYPO3 8.7
 */
class GeocodeLocationsCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController
{
    /**
     * @var \Evoweb\StoreFinder\Domain\Repository\LocationRepository
     */
    protected $locationRepository;

    /**
     * @var \Evoweb\StoreFinder\Service\GeocodeService
     */
    protected $geocodeService;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    protected $persistenceManager;

    public function injectGeocodeService(
        \Evoweb\StoreFinder\Service\GeocodeService $geocodeService
    ) {
        $this->geocodeService = $geocodeService;
    }

    public function injectLocationRepository(
        \Evoweb\StoreFinder\Domain\Repository\LocationRepository $locationRepository
    ) {
        $this->locationRepository = $locationRepository;
    }

    public function injectPersistenceManager(
        \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager
    ) {
        $this->persistenceManager = $persistenceManager;
    }

    public function geocodeCommand(): bool
    {
        $this->geocodeService->setSettings(
            \Evoweb\StoreFinder\Utility\ExtensionConfigurationUtility::getConfiguration()
        );

        $loopCount = 0;
        $locationsToGeocode = $this->locationRepository->findAllWithoutLatLon();
        /** @var \Evoweb\StoreFinder\Domain\Model\Constraint $location */
        foreach ($locationsToGeocode as $location) {
            $location = $this->geocodeService->geocodeAddress($location);

            if ($location->getLatitude() && $location->getLongitude()) {
                $location->setGeocode(0);
            }

            $this->locationRepository->update($location);
            $loopCount++;

            if ($loopCount > 9) {
                $this->persistenceManager->persistAll();
                $loopCount = 0;
            }
        }

        if ($loopCount) {
            $this->persistenceManager->persistAll();
        }

        return true;
    }
}
