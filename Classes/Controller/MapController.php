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
use Evoweb\StoreFinder\Domain\Repository\CategoryRepository;
use Evoweb\StoreFinder\Domain\Repository\LocationRepository;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Class MapController
 *
 * @package Evoweb\StoreFinder\Controller
 */
class MapController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * @var LocationRepository
     */
    public $locationRepository;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var \Evoweb\StoreFinder\Service\GeocodeService
     */
    protected $geocodeService;


    /**
     * @param LocationRepository $locationRepository
     */
    public function injectLocationRepository(LocationRepository $locationRepository)
    {
        $this->locationRepository = $locationRepository;
    }

    /**
     * @param CategoryRepository $categoryRepository
     */
    public function injectCategoryRepository(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param \Evoweb\StoreFinder\Service\GeocodeService $geocodeService
     */
    public function injectGeocodeService(\Evoweb\StoreFinder\Service\GeocodeService $geocodeService)
    {
        $this->geocodeService = $geocodeService;
    }


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
        $this->signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class
        );
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
            $afterSearch = 1;

            $search = $this->geocodeService->geocodeAddress($search);
            $this->view->assign('searchWasNotClearEnough', $this->geocodeService->hasMultipleResults);

            $locations = $this->locationRepository->findByConstraint($search);
            $result = $this->signalSlotDispatcher->dispatch(
                __CLASS__,
                'mapActionWithConstraint',
                [$search, $locations, $this]
            );
            /** @var Model\Constraint $search */
            $search = $result[0];
            /** @var QueryResultInterface $locations */
            $locations = $result[1];

            $center = $this->getCenter($search);
            $center = $this->setZoomLevel($center, $locations);
            $this->view->assign('center', $center);

            $this->view->assign('numberOfLocations', $locations->count());
            $this->view->assign('locations', $locations);
        } elseif ($this->settings['singleLocationId']) {
            /** @var Model\Constraint $search */
            $search = $this->objectManager->get(Model\Constraint::class);

            $location = $this->locationRepository->findByUid((int) $this->settings['singleLocationId']);
            $this->view->assign('numberOfLocations', is_object($location) ? 1 : 0);
            $this->view->assign('locations', [$location]);

            $center = $this->getCenter($search);
            $center = $this->setZoomLevel($center, [$location]);
            $this->view->assign('center', $center);
        } else {
            /** @var Model\Constraint $search */
            $search = $this->objectManager->get(Model\Constraint::class);

            if ($this->settings['showBeforeSearch'] & 2 && is_array($this->settings['defaultConstraint'])) {
                $search = $this->addDefaultConstraint($search);
                $search = $this->geocodeService->geocodeAddress($search);
                $this->view->assign('searchWasNotClearEnough', $this->geocodeService->hasMultipleResults);

                if ($this->settings['showLocationsForDefaultConstraint']) {
                    $locations = $this->locationRepository->findByConstraint($search);

                    $this->view->assign('numberOfLocations', $locations->count());
                    $this->view->assign('locations', $locations);
                } else {
                    $locations = [];
                }

                $center = $this->getCenter($search);
                $center = $this->setZoomLevel($center, $locations);
                $this->view->assign('center', $center);
            }

            if ($this->settings['showBeforeSearch'] & 4) {
                $this->locationRepository->setDefaultOrderings([
                    'zipcode' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
                    'city' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
                    'name' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
                ]);
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
    public function showAction(Model\Location $location = null)
    {
        if ($location === null) {
            if ($this->settings['location']) {
                $location = $this->locationRepository->findByUid((int) $this->settings['location']);
            }
        }

        if ($location !== null) {
            /** @var Model\Location $center */
            $center = $location;
            $center->setZoom($this->settings['zoom'] ? $this->settings['zoom'] : 15);

            $this->view->assign('center', $center);
            $this->view->assign('numberOfLocations', 1);
            $this->view->assign('locations', [$location]);
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
            /** @var \Evoweb\StoreFinder\Domain\Model\Location $center */
            $center = $queryResult->getFirst();
            return $center;
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
     * @param Model\Constraint $search
     *
     * @return Model\Constraint
     */
    protected function addDefaultConstraint($search)
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
     * Geocode requested address and use as center or fetch location that was
     * flagged as center. If
     *
     * @param Model\Constraint $constraint
     *
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @return Model\Location
     */
    public function getCenter(Model\Constraint $constraint = null)
    {
        $center = null;

        if ($constraint !== null) {
            if ($constraint->getLatitude() && $constraint->getLongitude()) {
                /** @var Model\Location $center */
                $center = $this->objectManager->get(Model\Location::class);
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
     * @param Model\Location|Model\Constraint $center
     * @param QueryResultInterface|array $locations
     *
     * @return Model\Location
     */
    public function setZoomLevel($center, $locations)
    {
        $locations = is_object($locations) ? $locations->toArray() : $locations;
        $radius = false;
        foreach ($locations as $location) {
            $distance = is_object($location) ? $location->getDistance() : $location['distance'];
            $radius = $distance > $radius ? $distance : $radius;
        }

        if ($radius === false) {
            $radius = $this->settings['radius'];
        }

        if ($radius > 500 && $radius <= 1000) {
            $zoom = 12;
        } elseif ($radius < 2) {
            $zoom = 2;
        } elseif ($radius < 3) {
            $zoom = 3;
        } elseif ($radius < 5) {
            $zoom = 4;
        } elseif ($radius < 15) {
            $zoom = 6;
        } elseif ($radius <= 25) {
            $zoom = 7;
        } elseif ($radius <= 100) {
            $zoom = 9;
        } elseif ($radius <= 500) {
            $zoom = 11;
        } else {
            $zoom = 13;
        }

        $center->setZoom(18 - $zoom);

        return $center;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @return string
     */
    protected function getErrorFlashMessage()
    {
        return '';
    }
}
