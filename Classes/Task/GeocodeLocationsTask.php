<?php
namespace Evoweb\StoreFinder\Task;

/**
 * Class GeocodeLocationsTask
 *
 * @package Evoweb\StoreFinder\Task
 */
class GeocodeLocationsTask extends \TYPO3\CMS\Scheduler\Task\AbstractTask {
	/**
	 * @return bool
	 */
	public function execute() {
		$globalConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['store_finder']);

		/**
		 * @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager
		 */
		$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

		/**
		 * @var \Evoweb\StoreFinder\Domain\Repository\LocationRepository $locationRepository
		 */
		$locationRepository = $objectManager->get('Evoweb\\StoreFinder\\Domain\\Repository\\LocationRepository');

		/**
		 * @var \Evoweb\StoreFinder\Service\GeocodeService $geocodeService
		 */
		$geocodeService = $objectManager->get('Evoweb\\StoreFinder\\Service\\GeocodeService', $globalConfiguration);

		/**
		 * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager
		 */
		$persistenceManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');

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

		return TRUE;
	}
}