<?php
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

class GeocodeLocationsCommand extends \Symfony\Component\Console\Command\Command
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

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $this->setAliases(['storefinder:geocode']);
        $this->setDescription('Query google geocode service to get lat/lon for locations that are not geocode already');
    }

    public function injectGeocodeService(
        \Evoweb\StoreFinder\Service\GeocodeService $geocodeService
    ) {
        $this->geocodeService = $geocodeService;
    }

    public function geocodeCommand(): bool
    {
        $this->geocodeService->setSettings(
            \Evoweb\StoreFinder\Utility\ExtensionConfigurationUtility::getConfiguration()
        );

        $loopCount = 0;
        $locationsToGeocode = $this->findAllWithoutLatLon();
        /** @var array $location */
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

    protected function findAllWithoutLatLon()
    {
        $queryBuilder = $this->getQueryBuilderForTable('tx_storefinder_domain_model_location');
        $queryBuilder
            ->select('*')
            ->from('tx_storefinder_domain_model_location')
            ->where(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq(
                        'latitude',
                        $queryBuilder->createNamedParameter('0.0000000', \PDO::PARAM_STR)
                    ),
                    $queryBuilder->expr()->eq(
                        'longitude',
                        $queryBuilder->createNamedParameter('0.0000000', \PDO::PARAM_STR)
                    )
                )
            );

        return $queryBuilder->execute();
    }

    protected function getQueryBuilderForTable(string $table): \TYPO3\CMS\Core\Database\Query\QueryBuilder
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Database\ConnectionPool::class
        )->getQueryBuilderForTable($table);
    }
}
