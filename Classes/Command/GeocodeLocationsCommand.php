<?php

declare(strict_types=1);

namespace Evoweb\StoreFinder\Command;

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

use Evoweb\StoreFinder\Domain\Model\Location;
use Evoweb\StoreFinder\Domain\Repository\LocationRepository;
use Evoweb\StoreFinder\Service\GeocodeService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class GeocodeLocationsCommand extends Command
{
    /**
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @var LocationRepository
     */
    protected $locationRepository;

    /**
     * @var GeocodeService
     */
    protected $geocodeService;

    public function __construct(
        LocationRepository $locationRepository,
        PersistenceManager $persistenceManager,
        GeocodeService $geocodeService,
        ExtensionConfiguration $extensionConfiguration
    ) {
        $this->locationRepository = $locationRepository;
        $this->persistenceManager = $persistenceManager;
        $this->geocodeService = $geocodeService;
        $this->geocodeService->setSettings($extensionConfiguration->get('store_finder') ?? []);
        parent::__construct(null);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $locationsToGeocode = $this->locationRepository->findAllWithoutLatLon();
        /** @var Location $location */
        foreach ($locationsToGeocode as $index => $location) {
            $location = $this->geocodeService->geocodeAddress($location);
            $location->setGeocode(($location->getLatitude() && $location->getLongitude()) ? 0 : 1);

            $this->locationRepository->update($location);

            if (($index % 50) == 0) {
                $this->persistenceManager->persistAll();
            }
        }

        $this->persistenceManager->persistAll();

        $io = new SymfonyStyle($input, $output);
        $io->comment('All locations geocoded');
        return 0;
    }
}
