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
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Http\JsonResponse;

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
        $filter = $request->getQueryParams()['filter'] ?? '';
        $cacheIdentifier = md5('store_finder' . $contentUid . serialize($filter ?? 'allLocationsCacheIdentifier'));

        if ($this->cache->has($cacheIdentifier)) {
            $locations = $this->cache->get($cacheIdentifier);
        } else {
            $settings = $this->contentRepository->getPluginSettingsByPluginUid($contentUid);

            $this->geocodeService->setSettings($settings);
            $this->locationRepository->setSettings($settings);
            /** @var Constraint $constraint */
            $constraint = $this->geocodeService->geocodeAddress($constraint);

            $locations = $this->locationRepository->findByConstraint($constraint, true);
            // @todo transform notes to html
            // @todo transform models to arrays
            $this->cache->set($cacheIdentifier, $locations);
        }

        $eventResult = $this->eventDispatcher->dispatch(
            new ModifyLocationsMiddlewareOutputEvent($this, $request, $locations)
        );

        return new JsonResponse($eventResult->getLocations());
    }
}
