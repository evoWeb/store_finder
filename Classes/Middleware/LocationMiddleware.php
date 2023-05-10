<?php

declare(strict_types=1);

namespace Evoweb\StoreFinder\Middleware;

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

use Evoweb\StoreFinder\Domain\Model\Constraint;
use Evoweb\StoreFinder\Domain\Repository\ContentRepository;
use Evoweb\StoreFinder\Domain\Repository\LocationRepository;
use Evoweb\StoreFinder\Event\ModifyLocationsMiddlewareOutputEvent;
use Evoweb\StoreFinder\Service\GeocodeService;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SJBR\StaticInfoTables\Domain\Model\Country;
use SJBR\StaticInfoTables\Domain\Model\CountryZone;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LocationMiddleware implements MiddlewareInterface
{
    public function __construct(
        protected ContentRepository $contentRepository,
        protected LocationRepository $locationRepository,
        protected GeocodeService $geocodeService,
        protected EventDispatcherInterface $eventDispatcher,
        protected FrontendInterface $cache,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = ltrim($request->getUri()->getPath(), '/');
        if ($path !== 'api/storefinder/locations') {
            return $handler->handle($request);
        }

        $contentUid = $request->getQueryParams()['contentUid'] ?? 0;
        $cacheIdentifier = md5('store_finder' . $contentUid . serialize($filter ?? 'allLocationsCacheIdentifier'));

        if ($this->cache->has($cacheIdentifier)) {
            $locations = $this->cache->get($cacheIdentifier);
        } else {
            $settings = $this->contentRepository->getPluginSettingsByPluginUid($contentUid);

            [$constraint, $settings] = $this->prepareConstraint($request, $settings);
            $locations = $this->locationRepository->getLocations($constraint, $settings);

            $this->cache->set($cacheIdentifier, $this->convertLocationsForResult($locations, $settings));
        }

        $eventResult = $this->eventDispatcher->dispatch(
            new ModifyLocationsMiddlewareOutputEvent($this, $request, $locations)
        );

        return new JsonResponse($eventResult->getLocations());
    }

    protected function prepareConstraint(ServerRequestInterface $request, array $settings): array
    {
        /** @var Constraint $constraint */
        $constraint = GeneralUtility::makeInstance(Constraint::class);
        $post = $request->getParsedBody();

        if (!empty($post['address'])) {
            $country = GeneralUtility::makeInstance(Country::class);
            $country->_setProperty('uid', 54);
            $constraint->setCountry($country);

            $countryZone = GeneralUtility::makeInstance(CountryZone::class);
            $countryZone->_setProperty('uid', 88);
            $constraint->setState($countryZone);

            $constraint->setCity($post['address']);
            $constraint->setZipcode($post['address']);
        }

        if (!empty($post['search'])) {
            $constraint->setSearch($post['search']);
        }

        if (!empty($post['categories'])) {
            $settings['limitResultsToCategories'] = true;
            $constraint->setCategory(GeneralUtility::intExplode(',', $post['categories'], true));
        }

        $this->geocodeService->setSettings($settings);
        /** @var Constraint $constraint */
        $constraint = $this->geocodeService->geocodeAddress($constraint);

        return [$constraint, $settings];
    }

    protected function convertLocationsForResult(array $locations, array $settings): array
    {
        // @todo transform notes to html
        // @todo transform models to arrays
        $fields = GeneralUtility::trimExplode(',', $settings['tables']['categories']['fields']);
        return $locations;
    }
}
