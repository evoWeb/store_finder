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
    public function __construct(
        protected LocationRepository $locationRepository,
        protected PersistenceManager $persistenceManager,
        protected GeocodeService $geocodeService,
        ExtensionConfiguration $extensionConfiguration
    ) {
        try {
            $this->geocodeService->setSettings($extensionConfiguration->get('store_finder') ?? []);
        } catch (\Exception $exception) {
            die('Error in $GLOBALS[\'TYPO3_CONF_VARS\'][\'EXTENSIONS\']: ' . $exception->getMessage());
        }
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->comment($this->getDescription());

        $locationsToGeocode = $this->locationRepository->findAllWithoutLatLon()->toArray();
        $locationCount = count($locationsToGeocode);

        $progressBar = $io->createProgressBar($locationCount);

        /** @var Location $location */
        foreach ($locationsToGeocode as $index => $location) {
            $location = $this->geocodeService->geocodeAddress($location);
            $location->setGeocode(($location->getLatitude() && $location->getLongitude()) ? 0 : 1);

            $this->locationRepository->update($location);

            if (($index % 50) == 0) {
                $this->persistenceManager->persistAll();
            }

            $progressBar->advance();
        }

        $this->persistenceManager->persistAll();

        $io->writeln('A total of ' . $locationCount . ' locations were imported');

        return self::SUCCESS;
    }
}
