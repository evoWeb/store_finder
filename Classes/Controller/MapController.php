<?php

declare(strict_types=1);

namespace Evoweb\StoreFinder\Controller;

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

use Doctrine\Common\Annotations\DocParser;
use Evoweb\StoreFinder\Controller\Event\MapGetLocationsByConstraintsEvent;
use Evoweb\StoreFinder\Domain\Repository\CountryRepository;
use Evoweb\StoreFinder\Validation\Validator\ConstraintValidator;
use Evoweb\StoreFinder\Validation\Validator\SettableInterface;
use Evoweb\StoreFinder\Domain\Model\Constraint;
use Evoweb\StoreFinder\Domain\Model\Location;
use SJBR\StaticInfoTables\Domain\Model\Country;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;

class MapController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * @var \Evoweb\StoreFinder\Domain\Repository\LocationRepository
     */
    public $locationRepository;

    /**
     * @var \Evoweb\StoreFinder\Domain\Repository\CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var \Evoweb\StoreFinder\Service\GeocodeService
     */
    protected $geocodeService;

    public function __construct(
        \Evoweb\StoreFinder\Domain\Repository\LocationRepository $locationRepository,
        \Evoweb\StoreFinder\Domain\Repository\CategoryRepository $categoryRepository,
        \Evoweb\StoreFinder\Service\GeocodeService $geocodeService
    ) {
        $this->locationRepository = $locationRepository;
        $this->categoryRepository = $categoryRepository;
        $this->geocodeService = $geocodeService;
    }

    protected function initializeActionMethodValidators()
    {
        if ($this->arguments->hasArgument('constraint')) {
            $this->modifyValidatorsBasedOnSettings(
                $this->arguments->getArgument('constraint'),
                $this->settings['validation'] ?? []
            );
        } else {
            parent::initializeActionMethodValidators();
        }
    }

    protected function modifyValidatorsBasedOnSettings(
        \TYPO3\CMS\Extbase\Mvc\Controller\Argument $argument,
        array $configuredValidators
    ) {
        $parser = new DocParser();

        /** @var ConstraintValidator $validator */
        $validator = GeneralUtility::makeInstance(ConstraintValidator::class);
        foreach ($configuredValidators as $fieldName => $configuredValidator) {
            if (!is_array($configuredValidator)) {
                $validatorInstance = $this->getValidatorByConfiguration(
                    $configuredValidator,
                    $parser
                );

                if ($validatorInstance instanceof SettableInterface) {
                    $validatorInstance->setPropertyName($fieldName);
                }
            } else {
                $validatorInstance = GeneralUtility::makeInstance(ConstraintValidator::class);
                foreach ($configuredValidator as $individualConfiguredValidator) {
                    $individualValidatorInstance = $this->getValidatorByConfiguration(
                        $individualConfiguredValidator,
                        $parser
                    );

                    if ($individualValidatorInstance instanceof SettableInterface) {
                        $individualValidatorInstance->setPropertyName($fieldName);
                    }

                    $validatorInstance->addValidator($individualValidatorInstance);
                }
            }

            $validator->addPropertyValidator($fieldName, $validatorInstance);
        }

        $argument->setValidator($validator);
    }

    /**
     * @param string $configuration
     * @param DocParser $parser
     *
     * @return ValidatorInterface
     */
    protected function getValidatorByConfiguration(string $configuration, DocParser $parser)
    {
        if (strpos($configuration, '"') === false && strpos($configuration, '(') === false) {
            $configuration = sprintf('"%s"', $configuration);
        }

        /** @var \TYPO3\CMS\Extbase\Annotation\Validate $validateAnnotation */
        $validateAnnotation = current($parser->parse(
            '@TYPO3\CMS\Extbase\Annotation\Validate(' . $configuration . ')'
        ));
        $validatorObjectName = \TYPO3\CMS\Extbase\Validation\ValidatorClassNameResolver::resolve(
            $validateAnnotation->validator
        );
        /** @var ValidatorInterface $validator */
        $validator = GeneralUtility::makeInstance($validatorObjectName, $validateAnnotation->options);
        return $validator;
    }

    protected function setTypeConverter()
    {
        if ($this->request->hasArgument('constraint')) {
            /** @var array $constraint */
            $constraint = $this->request->getArgument('constraint');
            if (!is_array($constraint['category'])) {
                $constraint['category'] = [$constraint['category']];
                $this->request->setArgument('constraint', $constraint);
            }

            /** @var PropertyMappingConfiguration $configuration */
            $configuration = $this->arguments['constraint']->getPropertyMappingConfiguration();
            $configuration->allowProperties('category');
            $configuration->setTypeConverterOption(
                PersistentObjectConverter::class,
                PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED,
                true
            );
        }
    }

    /**
     * Initializes the controller before invoking an action method. Override
     * this method to solve tasks which all actions have in common.
     */
    protected function initializeAction()
    {
        if (isset($this->settings['override']) && is_array($this->settings['override'])) {
            $override = $this->settings['override'];
            unset($this->settings['override']);

            $this->settings = array_merge($this->settings, $override);
        }

        $this->settings['allowedCountries'] = $this->settings['allowedCountries'] ?
            explode(',', $this->settings['allowedCountries']) :
            [];
        $this->geocodeService->setSettings($this->settings);
        $this->locationRepository->setSettings($this->settings);

        $this->setTypeConverter();
    }

    /**
     * Action responsible for rendering search, map and list partial
     *
     * @param ?Constraint $constraint
     *
     * @TYPO3\CMS\Extbase\Annotation\Validate("Evoweb\StoreFinder\Validation\Validator\Constraint", param="constraint")
     *
     * @return string
     */
    public function mapAction(Constraint $constraint = null): string
    {
        if ($constraint !== null) {
            $this->getLocationsByConstraints($constraint);
        } elseif ($this->settings['location']) {
            $this->forward('show');
        } else {
            $this->getLocationsByDefaultConstraints();
        }

        $this->addCategoryFromSettingsToView();
        $this->view->assign(
            'static_info_tables',
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('static_info_tables') ? 1 : 0
        );
        return trim($this->view->render());
    }

    /**
     * Action responsible for rendering search, map and list partial
     *
     * @param ?Constraint $constraint
     *
     * @TYPO3\CMS\Extbase\Annotation\Validate("Evoweb\StoreFinder\Validation\Validator\Constraint", param="constraint")
     *
     * @return string
     */
    public function cachedMapAction(Constraint $constraint = null): string
    {
        if ($constraint !== null) {
            $this->getLocationsByConstraints($constraint);
        } elseif ($this->settings['location']) {
            $this->forward('show');
        } else {
            $this->getLocationsByDefaultConstraints();
        }

        $this->addCategoryFromSettingsToView();
        $this->view->assign(
            'static_info_tables',
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('static_info_tables') ? 1 : 0
        );
        return trim($this->view->render());
    }

    protected function getLocationsByConstraints(Constraint $constraint)
    {
        $constraint = $this->addDefaultConstraint($constraint);
        $constraint = $this->geocodeService->geocodeAddress($constraint);
        $this->view->assign('searchWasNotClearEnough', $this->geocodeService->hasMultipleResults);

        /** @var Constraint $constraint */
        $locations = $this->locationRepository->findByConstraint($constraint);

        $event = new MapGetLocationsByConstraintsEvent($this, $locations, $constraint);
        $this->eventDispatcher->dispatch($event);
        $locations = $event->getLocations();
        $constraint = $event->getConstraint();

        $this->view->assign('afterSearch', 1);
        $this->view->assign('constraint', $constraint);
        $this->view->assign('locations', $locations);

        if (count($locations) > 0 || $constraint->isGeocoded()) {
            $center = $this->getCenterOfQueryResult($constraint, $locations);
            $center = $this->setZoomLevel($center, $locations);
            $this->view->assign('center', $center);
        }
    }

    protected function getLocationsByDefaultConstraints()
    {
        /** @var Constraint $constraint */
        $constraint = GeneralUtility::makeInstance(Constraint::class);
        $locations = false;

        if ($this->settings['showBeforeSearch'] & 2 && is_array($this->settings['defaultConstraint'])) {
            $constraint = $this->addDefaultConstraint($constraint);
            if ($this->settings['geocodeDefaultConstraint']) {
                $constraint = $this->geocodeService->geocodeAddress($constraint);
            }
            $this->view->assign('searchWasNotClearEnough', $this->geocodeService->hasMultipleResults);

            if ($this->settings['showLocationsForDefaultConstraint']) {
                $locations = $this->locationRepository->findByConstraint($constraint);
            }
        }

        if ($this->settings['showBeforeSearch'] & 4) {
            $this->locationRepository->setDefaultOrderings([
                'zipcode' => QueryInterface::ORDER_ASCENDING,
                'city' => QueryInterface::ORDER_ASCENDING,
                'name' => QueryInterface::ORDER_ASCENDING,
            ]);

            $locations = $this->locationRepository->findAll();
        }

        if (!$locations) {
            $locations = $this->locationRepository->findOneByUid(-1);
        }

        $event = new MapGetLocationsByConstraintsEvent($this, $locations, $constraint);
        $this->eventDispatcher->dispatch($event);
        $locations = $event->getLocations();
        $constraint = $event->getConstraint();

        $this->view->assign('afterSearch', 0);
        $this->view->assign('constraint', $constraint);
        $this->view->assign('locations', $locations);

        if (count($locations) > 0 || $constraint->isGeocoded()) {
            $center = $this->getCenterOfQueryResult($constraint, $locations);
            $center = $this->setZoomLevel($center, $locations);
            $this->view->assign('center', $center);
        }
    }

    public function showAction(Location $location = null)
    {
        if ($location === null) {
            if ($this->settings['location']) {
                $location = $this->locationRepository->findByUid((int) $this->settings['location']);
            }
        }

        if ($location !== null) {
            /** @var Location $center */
            $center = $location;
            $center->setZoom($this->settings['zoom'] ? (int)$this->settings['zoom'] : 15);

            $this->view->assign('center', $center);
            $this->view->assign('numberOfLocations', 1);
            $this->view->assign('locations', [$location]);
        }
    }

    protected function addCategoryFromSettingsToView()
    {
        if ($this->settings['categories']) {
            $categories = $this->categoryRepository->findByUids(
                GeneralUtility::intExplode(',', $this->settings['categories'], true)
            );

            $this->view->assign('categories', $categories);
        }
    }

    /**
     * Get center from query result based on center of all coordinates. If only one
     * is found this is used. In case none was found the center based on the request
     * gets calculated
     *
     * @param Constraint $constraint
     * @param QueryResultInterface $queryResult
     *
     * @return Location
     */
    protected function getCenterOfQueryResult(Constraint $constraint, QueryResultInterface $queryResult): Location
    {
        $count = $queryResult->count();
        /** @var $center Location */
        if ($count == 1) {
            $center = $queryResult->getFirst();
        } elseif (!$queryResult->count()) {
            $center = $this->getCenter($constraint);
        } else {
            $x = 0;
            $y = 0;
            $z = 0;

            /** @var Location $location */
            foreach ($queryResult as $location) {
                $latitude = $location->getLatitude() * M_PI / 180;
                $longitude = $location->getLongitude() * M_PI / 180;

                $x += cos($latitude) * cos($longitude);
                $y += cos($latitude) * sin($longitude);
                $z += sin($latitude);
            }

            $x /= $count;
            $y /= $count;
            $z /= $count;

            $centralLongitude = atan2($y, $x);
            $centralSquareRoot = sqrt($x * $x + $y * $y);
            $centralLatitude = atan2($z, $centralSquareRoot);

            $center = GeneralUtility::makeInstance(Location::class);
            $center->setLatitude($centralLatitude * 180 / M_PI);
            $center->setLongitude($centralLongitude * 180 / M_PI);
        }

        return $center;
    }

    /**
     * Add default constraints configured in typoscript and only set if property
     * in search is empty
     *
     * @param Constraint $search
     *
     * @return Constraint
     */
    protected function addDefaultConstraint(Constraint $search): Constraint
    {
        $defaultConstraint = $this->settings['defaultConstraint'];

        foreach ($defaultConstraint as $property => $value) {
            switch ($property) {
                case 'limit':
                case 'radius':
                case 'zoom':
                    $value = (int)$value;
                    break;

                case 'latitude':
                case 'longitude':
                    $value = (float)$value;
                    break;

                case 'country':
                    /** @var CountryRepository $countryRepository */
                    $countryRepository = GeneralUtility::getContainer()->get(CountryRepository::class);
                    /** @var $country Country */
                    if (intval($defaultConstraint['country'])) {
                        $value = $countryRepository->findByUid((int) $defaultConstraint['country']);
                    } elseif (strlen($defaultConstraint['country']) === 2) {
                        $value = $countryRepository->findByIsoCodeA2([$defaultConstraint['country']])->getFirst();
                    } elseif (strlen($defaultConstraint['country']) === 3) {
                        $value = $countryRepository->findByIsoCodeA3($defaultConstraint['country']);
                    }
                    break;
            }

            $getter = 'get' . ucfirst($property);
            $setter = 'set' . ucfirst($property);
            if ($value && method_exists($search, $setter) && !$search->{$getter}()) {
                $search->{$setter}($value);
            }
        }

        return $search;
    }

    /**
     * Geocode requested address and use as center or fetch location that was flagged as center.
     *
     * @param ?Constraint $constraint
     *
     * @return Location
     */
    public function getCenter(Constraint $constraint = null): Location
    {
        $center = null;

        if ($constraint !== null) {
            if (!$constraint->getLatitude() || !$constraint->getLongitude()) {
                $constraint = $this->geocodeService->geocodeAddress($constraint);
            }

            /** @var Location $center */
            $center = GeneralUtility::makeInstance(Location::class);
            $center->setLatitude($constraint->getLatitude());
            $center->setLongitude($constraint->getLongitude());
        }

        if ($center === null) {
            /** @var Constraint $center */
            $center = $this->locationRepository->findOneByCenter();
        }

        if ($center === null) {
            $center = $this->locationRepository->findCenterByLatitudeAndLongitude();
        }

        return $center;
    }

    /**
     * Set zoom level for map based on maximum radius
     *
     * @param Location $center
     * @param QueryResultInterface $locations
     *
     * @return Location
     */
    public function setZoomLevel(Location $center, $locations): Location
    {
        $radius = false;
        /** @var Location $location */
        foreach ($locations as $location) {
            $radius = max($radius, $location->getDistance());
        }

        if ($radius === false) {
            $radius = (int) $this->settings['defaultConstraint']['radius'];
        }

        if ($radius < 2) {
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
        } elseif ($radius <= 300) {
            $zoom = 10;
        } elseif ($radius <= 500) {
            $zoom = 11;
        } elseif ($radius > 500 && $radius <= 1000) {
            $zoom = 12;
        } else {
            $zoom = 13;
        }

        $center->setZoom(intval(18 - $zoom));

        return $center;
    }

    public function getView(): ViewInterface
    {
        return $this->view;
    }

    protected function getErrorFlashMessage()
    {
        return false;
    }
}
