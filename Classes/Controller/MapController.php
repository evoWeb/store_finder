<?php
namespace Evoweb\StoreFinder\Controller;
/***************************************************************
 * Copyright notice
 *
 * (c) 2013 Sebastian Fischer <typo3@evoweb.de>
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

use Evoweb\StoreFinder\Domain\Model;
use Evoweb\StoreFinder\Domain\Repository;

/**
 * Class MapController
 *
 * @package Evoweb\StoreFinder\Controller
 */
class MapController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {
	/**
	 * @var \Evoweb\StoreFinder\Domain\Repository\LocationRepository
	 * @inject
	 */
	protected $locationRepository;

	/**
	 * @var \Evoweb\StoreFinder\Domain\Repository\CategoryRepository
	 * @inject
	 */
	protected $categoryRepository;

	/**
	 * @var \Evoweb\StoreFinder\Service\GeocodeService
	 * @inject
	 */
	protected $geocodeService;


	/**
	 * Initializes the controller before invoking an action method.
	 *
	 * Override this method to solve tasks which all actions have in
	 * common.
	 *
	 * @return void
	 */
	protected function initializeAction() {
		if (isset($this->settings['override']) && is_array($this->settings['override'])) {
			$override = $this->settings['override'];
			unset($this->settings['override']);

			$this->settings = array_merge($this->settings, $override);
		}

		$this->settings['allowedCountries'] = explode(',', $this->settings['allowedCountries']);
		$this->locationRepository->setSettings($this->settings);
	}

	/**
	 * Action responsible for rendering search, map and list partial
	 *
	 * @param Model\Constraint $search
	 * @throws \BadFunctionCallException
	 * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
	 * @return void
	 * @validate $search Evoweb.StoreFinder:Constraint
	 */
	public function mapAction(Model\Constraint $search = NULL) {
		$afterSearch = 0;

		if ($search !== NULL) {
			$search = $this->geocodeService->geocodeAddress($search);
			$locations = $this->locationRepository->findByConstraint($search);

			$center = $this->getCenter($search);
			$center = $this->setZoomLevel($center, $search);

			$afterSearch = 1;

			$this->view->assign('center', $center);
			$this->view->assign('numberOfLocations', is_array($locations) ? count($locations) : $locations->count());
			$this->view->assign('locations', $locations);
		} elseif ($this->settings['singleLocationId']) {
			$location = $this->locationRepository->findByUid($this->settings['singleLocationId']);

			$search = $this->objectManager->get('Evoweb\\StoreFinder\\Domain\\Model\\Constraint');

			$this->view->assign('numberOfLocations', is_object($location) ? 1 : 0);
			$this->view->assign('locations', array($location));
		} else {
			$search = $this->objectManager->get('Evoweb\\StoreFinder\\Domain\\Model\\Constraint');
		}

		$this->addCategoryFilterToView();
		$this->view->assign('afterSearch', $afterSearch);
		$this->view->assign('search', $search);
		$this->view->assign('static_info_tables', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('static_info_tables') ? 1 : 0);
	}


	/**
	 * Add categories give in settings to view
	 *
	 * @return void
	 */
	protected function addCategoryFilterToView() {
		if ($this->settings['categories']) {
			$categories = $this->categoryRepository->findByUids($this->settings['categories']);

			$this->view->assign('categories', $categories);
		}
	}

	/**
	 * Get center from query result based on center of all coordinates. If only one
	 * is found this is used. In case none was found the center based on the request
	 * gets calculated
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $queryResult
	 * @return Model\Location
	 */
	protected function getCenterOfQueryResult($queryResult) {
		if ($queryResult->count() == 1) {
			return $queryResult->getFirst();
		} elseif (!$queryResult->count()) {
			return $this->getCenter();
		}

		/** @var Model\Location $center */
		$center = $this->objectManager->get('Evoweb\StoreFinder\Domain\Model\Location');

		$x = $y = $z = 0;
		/** @var Model\Location $location */
		foreach ($queryResult as $location) {
			$x += cos($location->getLatitude()) * cos($location->getLongitude());
			$y += cos($location->getLatitude()) * sin($location->getLongitude());
			$z += sin($location->getLatitude());
		}

		$x /= $queryResult->count();
		$y /= $queryResult->count();
		$z /= $queryResult->count();

		$center->setLongitude(atan2($y, $x));
		$center->setLatitude(atan2($z, sqrt($x * $x + $y * $y)));

		return $center;
	}

	/**
	 * Geocode requested address and use as center or fetch location that was
	 * flagged as center. If
	 *
	 * @param Model\Constraint $constraint
	 * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
	 * @return Model\Location
	 */
	protected function getCenter(Model\Constraint $constraint = NULL) {
		$center = NULL;

		if ($constraint !== NULL) {
			if ($constraint->getLatitude() && $constraint->getLongitude()) {
				/** @var Model\Location $center */
				$center = $this->objectManager->get('Evoweb\StoreFinder\Domain\Model\Location');
				$center->setLatitude($constraint->getLatitude());
				$center->setLongitude($constraint->getLongitude());
			} else {
				$center = $this->geocodeService->geocodeAddress($constraint);
			}
		}

		if ($center === NULL) {
			$center = $this->locationRepository->findOneByCenter();
		}

		if ($center === NULL) {
			$center = $this->locationRepository->findCenterByLatitudeAndLongitude();
		}

		return $center;
	}

	/**
	 * Set zoom level for map based on radius
	 *
	 * @param Model\Location|Model\Constraint $location
	 * @param Model\Constraint $constraint
	 * @return Model\Location
	 */
	protected function setZoomLevel($location, Model\Constraint $constraint) {
		$radius = $constraint->getRadius();
		if ($radius > 500 && $radius <= 1000) {
			$zoom = 12;
		} elseif ($radius < 2) {
			$zoom = 2;
		} elseif ($radius < 3) {
			$zoom = 3;
		} elseif ($radius < 5) {
			$zoom = 4;
		} elseif ($radius <= 25) {
			$zoom = 7;
		} elseif ($radius <= 100) {
			$zoom = 9;
		} elseif ($radius <= 500) {
			$zoom = 11;
		} else {
			$zoom = 13;
		}

		$location->setZoom(18 - $zoom);
		return $location;
	}
}