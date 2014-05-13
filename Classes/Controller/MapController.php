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
class MapController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
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
     * Override this method to solve tasks which all actions have in
     * common.
     *
     * @return void
     */
    protected function initializeAction()
    {
        if (isset($this->settings['override']) && is_array($this->settings['override'])) {
            $override = $this->settings['override'];
            unset($this->settings['override']);

            $this->settings = array_merge($this->settings, $override);
        }

        $this->settings['allowedCountries'] = explode(',', $this->settings['allowedCountries']);
        $this->geocodeService->setSettings($this->settings);
        $this->locationRepository->setSettings($this->settings);
    }

    /**
     * Action responsible for rendering search, map and list partial
     *
     * @param Model\Constraint $search
     *
     * @throws \BadFunctionCallException
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @return void
     * @validate $search Evoweb.StoreFinder:Constraint
     */
    public function mapAction(Model\Constraint $search = null)
    {
        $afterSearch = 0;

        if ($search !== null) {
            $search = $this->geocodeService->geocodeAddress($search);

            $center = $this->getCenter($search);
            $center = $this->setZoomLevel($center, $search);
            $this->view->assign('center', $center);

            $afterSearch = 1;

            $locations = $this->locationRepository->findByConstraint($search);
            // manual rewind needed because fluid doesn't do it
            $locations->rewind();
            $this->view->assign('numberOfLocations', $locations->count());
            $this->view->assign('locations', $locations);
        } elseif ($this->settings['singleLocationId']) {
            /** @var Model\Constraint $search */
            $search = $this->objectManager->get('Evoweb\\StoreFinder\\Domain\\Model\\Constraint');

            $center = $this->getCenter($search);
            $center = $this->setZoomLevel($center, $search);
            $this->view->assign('center', $center);

            $location = $this->locationRepository->findByUid((int) $this->settings['singleLocationId']);
            $this->view->assign('numberOfLocations', is_object($location) ? 1 : 0);
            $this->view->assign('locations', array($location));
        } else {
            /** @var Model\Constraint $search */
            $search = $this->objectManager->get('Evoweb\\StoreFinder\\Domain\\Model\\Constraint');

            if ($this->settings['showBeforeSearch'] & 2 && is_array($this->settings['defaultConstraint'])) {
                $search = $this->addDefaultConstraint($search);
                $search = $this->geocodeService->geocodeAddress($search);

                $center = $this->getCenter($search);
                $center = $this->setZoomLevel($center, $search);
                $this->view->assign('center', $center);

                if ($this->settings['showLocationsForDefaultConstraint']) {
                    $locations = $this->locationRepository->findByConstraint($search);
                    // manual rewind needed because fluid doesn't do it
                    $locations->rewind();
                    $this->view->assign('numberOfLocations', $locations->count());
                    $this->view->assign('locations', $locations);
                }
            }

            if ($this->settings['showBeforeSearch'] & 4) {
                $this->locationRepository->setDefaultOrderings(array(
                    'zipcode' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
                    'city' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
                    'name' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
                ));
                $locations = $this->locationRepository->findAll();

                $this->view->assign('locations', $locations);
            }
        }

        $this->addCategoryFilterToView();
        $this->view->assign('afterSearch', $afterSearch);
        $this->view->assign('search', $search);
        $this->view->assign(
            'static_info_tables',
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('static_info_tables') ? 1 : 0
        );
    }

	/**
	 * Render a map with only one location
	 *
	 * @param Model\Location $location
	 * @return void
	 */
	public function showAction(Model\Location $location = NULL) {
		if ($location === NULL) {
			if ($this->settings['location']) {
				$location = $this->locationRepository->findByUid((int) $this->settings['location']);
			}
		}

		if ($location !== NULL) {
			/** @var Model\Location $center */
			$center = $location;
			$center->setZoom($this->settings['zoom'] ? $this->settings['zoom'] : 15);

			$this->view->assign('center', $center);
			$this->view->assign('numberOfLocations', 1);
			$this->view->assign('locations', array($location));
		}
	}


    /**
     * Add categories give in settings to view
     *
     * @return void
     */
    protected function addCategoryFilterToView()
    {
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
     *
     * @return Model\Location
     */
    protected function getCenterOfQueryResult($queryResult)
    {
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
     *
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @return Model\Location
     */
    protected function getCenter(Model\Constraint $constraint = null)
    {
        $center = null;

        if ($constraint !== null) {
            if ($constraint->getLatitude() && $constraint->getLongitude()) {
                /** @var Model\Location $center */
                $center = $this->objectManager->get('Evoweb\\StoreFinder\\Domain\\Model\\Location');
                $center->setLatitude($constraint->getLatitude());
                $center->setLongitude($constraint->getLongitude());
            } else {
                $center = $this->geocodeService->geocodeAddress($constraint);
            }
        }

        if ($center === null) {
            $center = $this->locationRepository->findOneByCenter();
        }

        if ($center === null) {
            $center = $this->locationRepository->findCenterByLatitudeAndLongitude();
        }

        return $center;
    }

    /**
     * Set zoom level for map based on radius
     *
     * @param Model\Location|Model\Constraint $location
     * @param Model\Constraint $constraint
     *
     * @return Model\Location
     */
    protected function setZoomLevel($location, Model\Constraint $constraint)
    {
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

    /**
     * @param Model\Constraint $search
     *
     * @return Model\Constraint
     */
    private function addDefaultConstraint($search)
    {
        $defaultConstraint = $this->settings['defaultConstraint'];

        foreach ($defaultConstraint as $property => $value) {
            $setter = 'set' . ucfirst($property);
            if (method_exists($search, $setter)) {
                $search->{$setter}($value);
            }
        }

        return $search;
    }

    /**
     * @return string
     */
    protected function getErrorFlashMessage()
    {
        return '';
    }
}
