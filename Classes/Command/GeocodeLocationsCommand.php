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

use Evoweb\StoreFinder\Domain\Repository\LocationRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class GeocodeLocationsCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    public function __construct(string $name = null)
    {
        parent::__construct($name);
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
    }

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $this->setAliases(['storefinder:geocode']);
        $this->setDescription('Query google geocode service to get lat/lon for locations that are not geocode already');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var PersistenceManager $persistenceManager */
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $locationRepository = $this->getLocationRepository();
        $geocodeService = $this->getGeocodeService();

        $locationsToGeocode = $locationRepository->findAllWithoutLatLon();
        /** @var \Evoweb\StoreFinder\Domain\Model\Location $location */
        foreach ($locationsToGeocode as $index => $location) {
            $location = $geocodeService->geocodeAddress($location);

            $location->setGeocode(($location->getLatitude() && $location->getLongitude()) ? 0 : 1);

            $locationRepository->update($location);

            if (($index % 50) == 0) {
                $persistenceManager->persistAll();
            }
        }

        $persistenceManager->persistAll();

        $io = new SymfonyStyle($input, $output);
        $io->comment('All locations geocoded');
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
