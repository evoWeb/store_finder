<?php
declare(strict_types = 1);
namespace Evoweb\StoreFinder\Command;

/**
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class GeocodeLocationsCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $this->setAliases(['storefinder:geocode']);
        $this->setDescription('Query google geocode service to get lat/lon for locations that are not geocode already');
    }

    protected function getGeocodeService(): \Evoweb\StoreFinder\Service\GeocodeService
    {
        /** @var \Evoweb\StoreFinder\Service\GeocodeService $geocodeService */
        $geocodeService = GeneralUtility::makeInstance(\Evoweb\StoreFinder\Service\GeocodeService::class);
        /** @var \Evoweb\StoreFinder\Cache\CoordinatesCache $coordinatesCache */
        $coordinatesCache = GeneralUtility::makeInstance(\Evoweb\StoreFinder\Cache\CoordinatesCache::class);
        $geocodeService->injectCoordinatesCache($coordinatesCache);
        $geocodeService->setSettings(
            \Evoweb\StoreFinder\Utility\ExtensionConfigurationUtility::getConfiguration()
        );
        return $geocodeService;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $geocodeService = $this->getGeocodeService();
        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        /** @var \Evoweb\StoreFinder\Domain\Repository\LocationRepository $locationRepository */
        $locationRepository = $objectManager->get(
            \Evoweb\StoreFinder\Domain\Repository\LocationRepository::class
        );
        /** @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager */
        $persistenceManager = $objectManager->get(
            \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager::class
        );

        $loopCount = 0;
        $locationsToGeocode = $locationRepository->findAllWithoutLatLon();
        /** @var \Evoweb\StoreFinder\Domain\Model\Location $location */
        foreach ($locationsToGeocode as $location) {
            $location = $geocodeService->geocodeAddress($location);

            if ($location->getLatitude() && $location->getLongitude()) {
                $location->setGeocode(0);
            } else {
                $location->setGeocode(1);
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

        $io->comment('All locations geocoded');
    }
}
