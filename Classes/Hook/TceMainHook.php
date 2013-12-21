<?php
namespace Evoweb\StoreFinder\Hook;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Sebastian Fischer <typo3@evoweb.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

class TceMainHook {
	/**
	 * @var array
	 */
	protected $configuration = array();

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected $objectManager = NULL;

	/**
	 * @var \Evoweb\StoreFinder\Domain\Repository\LocationRepository
	 */
	protected $repository = NULL;

	/**
	 * @param string $status
	 * @param string $table
	 * @param string $id
	 * @param array $fieldArray
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler $parentObject
	 */
	public function processDatamap_afterDatabaseOperations($status, $table, $id, $fieldArray, $parentObject) {
		$id = $this->remapId($id, $table, $parentObject);

		if ($table === 'tx_storefinder_domain_model_location') {
			$this->initializeConfiguration();
			$location = $this->fetchLocation($id);

			if ($location !== NULL) {
				$this->storeLocation($this->setCoordinates($location));
			}
		}
	}

	/**
	 * @param string $id
	 * @param string $table
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler $parentObject
	 * @return integer
	 */
	protected function remapId($id, &$table, $parentObject) {
		if (array_key_exists($id, $parentObject->substNEWwithIDs)) {
			$table = $parentObject->substNEWwithIDs_table[$id];
			$id = $parentObject->substNEWwithIDs[$id];
		}

		return $id;
	}

	/**
	 * @return void
	 */
	protected function initializeConfiguration() {
		$this->configuration = \Evoweb\StoreFinder\Utility\ExtensionConfiguration::getConfiguration();
	}

	/**
	 * @param integer $uid
	 * @return \Evoweb\StoreFinder\Domain\Model\Location
	 */
	protected function fetchLocation($uid) {
		return $this->getRepository()->findByUid($uid);
	}

	/**
	 * @return \Evoweb\StoreFinder\Domain\Repository\LocationRepository
	 */
	protected function getRepository() {
		if ($this->repository === NULL) {
			$this->repository = $this->getObjectManager()->get('Evoweb\StoreFinder\Domain\Repository\LocationRepository');
		}

		return $this->repository;
	}

	/**
	 * @return \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected function getObjectManager() {
		if ($this->objectManager === NULL) {
			$this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		}

		return $this->objectManager;
	}

	/**
	 * @param \Evoweb\StoreFinder\Domain\Model\Location $location
	 * @return \Evoweb\StoreFinder\Domain\Model\Location
	 */
	protected function setCoordinates(\Evoweb\StoreFinder\Domain\Model\Location $location) {
		return $this->getObjectManager()
			->get('Evoweb\StoreFinder\Service\GeocodeService', $this->configuration)
			->geocodeAddress($location);
	}

	/**
	 * @param \Evoweb\StoreFinder\Domain\Model\Location $location
	 * @return void
	 */
	protected function storeLocation(\Evoweb\StoreFinder\Domain\Model\Location $location) {
		$this->getRepository()->update($location);
		/** @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistanceManager */
		$persistanceManager = $this->getObjectManager()->get('TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager');
		$persistanceManager->persistAll();
	}
}

?>