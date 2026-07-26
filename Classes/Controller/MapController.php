<?php

declare(strict_types=1);

/*
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Evoweb\StoreFinder\Controller;

use Evoweb\StoreFinder\Controller\Event\MapGetLocationsByConstraintsEvent;
use Evoweb\StoreFinder\Domain\Model\Constraint;
use Evoweb\StoreFinder\Domain\Model\Location;
use Evoweb\StoreFinder\Domain\Repository\CategoryRepository;
use Evoweb\StoreFinder\Domain\Repository\LocationRepository;
use Evoweb\StoreFinder\Property\TypeConverter\CountryConverter;
use Evoweb\StoreFinder\Services\GeocodeService;
use Evoweb\StoreFinder\Services\ModifyValidator;
use Evoweb\StoreFinder\Validation\Validator\ConstraintValidator;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Country\CountryProvider;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\CMS\Extbase\Attribute as Extbase;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception as Exception;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class MapController extends ActionController
{
    public function __construct(
        protected LocationRepository $locationRepository,
        protected CategoryRepository $categoryRepository,
        protected CountryProvider $countryProvider,
        protected GeocodeService $geocodeService,
        protected ModifyValidator $modifyValidator,
    ) {}

    protected function initializeActionMethodValidators(): void
    {
        if ($this->modifyValidator->shouldValidationBeModified($this->arguments, $this->settings)) {
            $this->arguments = $this->modifyValidator->modifyArgumentValidators(
                $this->arguments,
                $this->request,
                $this->settings,
            );
        } else {
            parent::initializeActionMethodValidators();
        }
    }

    protected function setTypeConverter(): void
    {
        $argumentName = 'constraint';
        if ($this->request->hasArgument($argumentName)) {
            /** @var array<string, mixed> $constraint */
            $constraint = $this->request->getArgument($argumentName);
            if (!is_array($constraint['category'] ?? '')) {
                $constraint['category'] = array_filter(
                    explode(',', $this->toStringOrDefault($constraint['category'] ?? '')),
                );
                /** @var ExtbaseRequestParameters $extbaseAttribute */
                $extbaseAttribute = $this->request->getAttribute('extbase');
                $extbaseAttribute->setArgument($argumentName, $constraint);
            }

            if ($this->arguments->hasArgument($argumentName)) {
                $configuration = $this->arguments[$argumentName]->getPropertyMappingConfiguration();
                $this->getPropertyMappingConfiguration($configuration);
            }
        }
    }

    protected function getPropertyMappingConfiguration(
        ?PropertyMappingConfiguration $configuration
    ): PropertyMappingConfiguration {
        $configuration ??= new PropertyMappingConfiguration();
        $configuration->allowProperties('category');
        $configuration->setTypeConverterOption(
            PersistentObjectConverter::class,
            (string)PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED,
            true,
        );
        $configuration->forProperty('country')
            ->setTypeConverterOptions(
                CountryConverter::class,
                [],
            );

        return $configuration;
    }

    protected function initializeAction(): void
    {
        if (is_array($this->settings['override'] ?? false)) {
            $override = $this->settings['override'];
            unset($this->settings['override']);

            $this->settings = array_merge($this->settings, $override);
        }

        $this->settings['allowedCountries'] = explode(',', $this->settings['allowedCountries'] ?? '');
        $this->settings['mapConfiguration']['libraries'] = $this->settings['mapConfiguration']['libraries']
            ? explode(',', $this->settings['mapConfiguration']['libraries'] ?? '')
            : [];

        $this->geocodeService->setSettings($this->settings);
        $this->locationRepository->setSettings($this->settings);

        $this->setTypeConverter();
    }

    protected function initializeView(): void
    {
        $currentContentObject = $this->request->getAttribute('currentContentObject');
        $this->view->assign(
            'cObjectData',
            $currentContentObject instanceof ContentObjectRenderer ? $currentContentObject->data : null,
        );
    }

    /**
     * Action responsible for rendering search, map and list partial
     */
    public function mapAction(): ResponseInterface
    {
        if ($this->settings['location'] ?? false) {
            return new ForwardResponse('show');
        }

        [$locations, $constraint] = $this->getLocationsByDefaultConstraints();

        $event = new MapGetLocationsByConstraintsEvent($this, $locations, $constraint);
        $this->eventDispatcher->dispatch($event);
        $locations = $event->getLocations();
        $constraint = $event->getConstraint();

        $this->view->assign('afterSearch', 0);
        $this->view->assign('constraint', $constraint);
        $this->view->assign('locations', $locations);

        if (count($locations) || $constraint->isGeocoded()) {
            $center = $this->getCenterOfQueryResult($constraint, $locations);
            $center = $this->setZoomLevel($center, $locations);
            $this->view->assign('center', $center);
        }

        $this->addCategoryFromSettingsToView();
        $this->addPaginator($locations);

        return new HtmlResponse($this->view->render());
    }

    /**
     * Action responsible for rendering cached search, map and list partial
     */
    public function cachedMapAction(): ResponseInterface
    {
        if ($this->settings['location'] ?? false) {
            return new ForwardResponse('show');
        }

        [$locations, $constraint] = $this->getLocationsByDefaultConstraints();

        $event = new MapGetLocationsByConstraintsEvent($this, $locations, $constraint);
        $this->eventDispatcher->dispatch($event);
        $locations = $event->getLocations();
        $constraint = $event->getConstraint();

        $this->view->assign('afterSearch', 0);
        $this->view->assign('constraint', $constraint);
        $this->view->assign('locations', $locations);

        if (count($locations) || $constraint->isGeocoded()) {
            $center = $this->getCenterOfQueryResult($constraint, $locations);
            $center = $this->setZoomLevel($center, $locations);
            $this->view->assign('center', $center);
        }

        $this->addCategoryFromSettingsToView();
        $this->addPaginator($locations);

        return new HtmlResponse($this->view->render());
    }

    /**
     * Action responsible for rendering search, map and list partial
     */
    #[Extbase\Validate(['param' => 'constraint', 'validator' => ConstraintValidator::class])]
    public function searchAction(Constraint $constraint): ResponseInterface
    {
        [$locations, $constraint] = $this->getLocationsByConstraints($constraint);

        $event = new MapGetLocationsByConstraintsEvent($this, $locations, $constraint);
        $this->eventDispatcher->dispatch($event);
        $locations = $event->getLocations();
        $constraint = $event->getConstraint();

        $this->view->assign('afterSearch', 1);
        $this->view->assign('constraint', $constraint);
        $this->view->assign('locations', $locations);

        if (count($locations) || $constraint->isGeocoded()) {
            $center = $this->getCenterOfQueryResult($constraint, $locations);
            $center = $this->setZoomLevel($center, $locations);
            $this->view->assign('center', $center);
        }

        $this->addCategoryFromSettingsToView();
        $this->addPaginator($locations);

        return new HtmlResponse($this->view->render());
    }

    /**
     * @return array{0: Location[], 1: Constraint}
     * @throws Exception
     */
    protected function getLocationsByConstraints(Constraint $constraint): array
    {
        if ($this->settings['disableLocationFetchLogic'] ?? false) {
            return [[], $constraint];
        }

        /** @var Constraint $constraint */
        $constraint = $this->geocodeService->geocodeAddress($constraint);
        $constraint = $this->addDefaultConstraint($constraint);

        /** @var Location[] $locations */
        $locations = $this->locationRepository->findByConstraint($constraint);

        return [$locations, $constraint];
    }

    /**
     * @return array{0: Location[], 1: Constraint}
     * @throws Exception
     */
    protected function getLocationsByDefaultConstraints(): array
    {
        /** @var Location[] $locations */
        $locations = [];
        /** @var Constraint $constraint */
        $constraint = GeneralUtility::makeInstance(Constraint::class);

        if ($this->settings['disableLocationFetchLogic'] ?? false) {
            return [$locations, $constraint];
        }

        if (
            ($this->settings['showBeforeSearch'] ?? 0) & 2
            && is_array($this->settings['defaultConstraint'] ?? false)
        ) {
            $constraint = $this->addDefaultConstraint($constraint);
            if ($this->settings['geocodeDefaultConstraint'] ?? false) {
                $geocodedConstraint = $this->geocodeService->geocodeAddress($constraint);
                if ($geocodedConstraint instanceof Constraint) {
                    $constraint = $geocodedConstraint;
                }
            }

            if ($this->settings['showLocationsForDefaultConstraint'] ?? false) {
                /** @var Location[] $locations */
                $locations = $this->locationRepository->findByConstraint($constraint);
            }
        }

        if (($this->settings['showBeforeSearch'] ?? 0) & 4) {
            $this->locationRepository->setDefaultOrderings([
                'country' => QueryInterface::ORDER_DESCENDING,
                'zipcode' => QueryInterface::ORDER_ASCENDING,
                'city' => QueryInterface::ORDER_ASCENDING,
                'name' => QueryInterface::ORDER_ASCENDING,
            ]);

            /** @var QueryResultInterface<int, Location> $queryResult */
            $queryResult = $this->locationRepository->findAll();
            $locations = $queryResult->toArray();
        }

        return [$locations, $constraint];
    }

    /**
     * @param array<string, array<string, mixed>> $settings
     */
    protected function isDisabledFetchLocation(string $action, array $settings): bool
    {
        return in_array(
            str_replace('Action', '', $action),
            ($settings['disableFetchLocationInAction'] ?? [])
        );
    }

    public function showAction(?Location $location = null): ResponseInterface
    {
        if ($location === null) {
            $location = $this->locationRepository->findOneByUid((int)($this->settings['location'] ?? -1));
        } else {
            $location = $this->locationRepository->findOneByUid($location->getUid() ?? 0);
        }

        $this->view->assign('afterSearch', 1);
        $this->view->assign('locations', [$location]);

        if ($location) {
            $center = $this->getCenterOfQueryResult($location, []);
            $center = $this->setZoomLevel($center, []);
            $this->view->assign('center', $center);
        }

        return new HtmlResponse($this->view->render());
    }

    protected function addCategoryFromSettingsToView(): void
    {
        if ($this->settings['categories'] ?? false) {
            $categories = $this->categoryRepository->findByUids(
                GeneralUtility::intExplode(',', $this->settings['categories'], true),
            );

            $this->view->assign('categories', $categories);
        }
    }

    /**
     * Get center from a query result based on a center of all coordinates. If only one
     * is found, this is used. In case none was found, the center based on the request
     * gets calculated
     * @param Location[] $locations
     */
    protected function getCenterOfQueryResult(Location $constraint, array $locations): Location
    {
        $count = count($locations);
        if ($count == 0) {
            $center = $this->getCenter($constraint);
        } elseif ($count == 1) {
            $center = reset($locations);
        } else {
            $x = 0;
            $y = 0;
            $z = 0;

            foreach ($locations as $location) {
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
     * Add default constraints configured in TypoScript and only set if the property
     * in search is empty
     */
    protected function addDefaultConstraint(Constraint $search): Constraint
    {
        $defaultConstraint = $this->settings['defaultConstraint'] ?? [];

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
                    if (strlen($defaultConstraint['country']) !== 2) {
                        throw new \Exception('Invalid country code provided in settings.defaultConstraint');
                    }
                    $value = $this->countryProvider->getByIsoCode($defaultConstraint['country']);
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
     * Geocode requested address and use as a center or fetch location that was flagged as a center.
     */
    public function getCenter(?Location $constraint = null): Location
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

        if (!($center instanceof Location)) {
            $center = $this->locationRepository->findCenterByLatitudeAndLongitude();
        }

        return $center;
    }

    /**
     * Set the zoom level for a map based on the maximum radius
     * @param Location[] $locations
     */
    public function setZoomLevel(Location $center, array $locations): Location
    {
        $radius = 0;
        foreach ($locations as $location) {
            $radius = max($radius, $location->getDistance());
        }

        if ($radius === 0) {
            $radius = (int)($this->settings['defaultConstraint']['radius'] ?? 0);
        }

        if ($radius < 2) {
            $zoom = 2;
        } elseif ($radius < 3) {
            $zoom = 3;
        } elseif ($radius < 5) {
            $zoom = 4;
        } elseif ($radius < 15) {
            $zoom = 6;
        } elseif ($radius <= 50) {
            $zoom = 7;
        } elseif ($radius <= 100) {
            $zoom = 9;
        } elseif ($radius <= 300) {
            $zoom = 10;
        } elseif ($radius <= 500) {
            $zoom = 11;
        } elseif ($radius <= 1000) {
            $zoom = 12;
        } else {
            $zoom = 13;
        }

        $center->setZoom(18 - $zoom);

        return $center;
    }

    /**
     * @param Location[] $locations
     */
    protected function addPaginator(array $locations): void
    {
        if ($this->settings['addPaginator'] ?? false) {
            $currentPage = $this->request->hasArgument('currentPage')
                ? $this->toIntOrDefault($this->request->getArgument('currentPage'), 1) : 1;

            $resultPaginator = new ArrayPaginator(
                $locations,
                $currentPage,
                (int)($this->settings['limit'] ?? 10),
            );
            $pagination = new SimplePagination($resultPaginator);

            $this->view->assignMultiple(
                [
                    'paginator' => $resultPaginator,
                    'pagination' => $pagination,
                    'pages' => range(1, $pagination->getLastPageNumber()),
                ],
            );
        }
    }

    public function getView(): ViewInterface
    {
        return $this->view;
    }

    protected function getErrorFlashMessage(): bool
    {
        return false;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    public function getActionMethodName(): string
    {
        return $this->actionMethodName;
    }

    public function getArguments(): Arguments
    {
        return $this->arguments;
    }

    protected function toStringOrDefault(mixed $value, string $default = ''): string
    {
        return is_scalar($value) ? (string)$value : $default;
    }

    protected function toIntOrDefault(mixed $value, int $default = 0): int
    {
        return is_numeric($value) ? (int)$value : $default;
    }
}
