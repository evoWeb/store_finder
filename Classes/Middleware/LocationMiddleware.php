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
use Evoweb\StoreFinder\Domain\Repository\CountryRepository;
use Evoweb\StoreFinder\Domain\Repository\LocationRepository;
use Evoweb\StoreFinder\Event\ModifyLocationsMiddlewareOutputEvent;
use Evoweb\StoreFinder\Service\GeocodeService;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SJBR\StaticInfoTables\Domain\Repository\CountryZoneRepository;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class LocationMiddleware implements MiddlewareInterface
{
    protected ContentRepository $contentRepository;

    protected LocationRepository $locationRepository;

    protected GeocodeService $geocodeService;

    protected EventDispatcherInterface $eventDispatcher;

    protected FrontendInterface $cache;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = ltrim($request->getUri()->getPath(), '/');
        $queryParams = $request->getQueryParams();
        if (!str_starts_with($path, 'api/storefinder/') || ($queryParams['action'] ?? '') !== 'locations') {
            return $handler->handle($request);
        }
        $this->initializeObject();

        $contentUid = $queryParams['contentUid'] ?? 0;
        $cacheIdentifier = md5('store_finder' . $contentUid . serialize($filter ?? 'allLocationsCacheIdentifier'));

        if (empty($request->getParsedBody()) && $this->cache->has($cacheIdentifier)) {
            $locations = $this->cache->get($cacheIdentifier);
        } else {
            $settings = $this->contentRepository->getPluginSettingsByPluginUid((int)$contentUid);

            $constraint = $this->prepareConstraint($request, $settings);
            $this->locationRepository->setSettings($settings);
            $locations = $this->locationRepository->getLocations($constraint);
            $locations = $this->convertLocationsForResult($locations, $settings, $request);

            $this->cache->set($cacheIdentifier, $locations);
        }

        $eventResult = $this->eventDispatcher->dispatch(
            new ModifyLocationsMiddlewareOutputEvent($this, $request, $locations)
        );

        return new JsonResponse($eventResult->getLocations());
    }

    protected function initializeObject(): void
    {
        $this->contentRepository = GeneralUtility::makeInstance(ContentRepository::class);
        $this->locationRepository = GeneralUtility::makeInstance(LocationRepository::class);
        $this->geocodeService = GeneralUtility::makeInstance(GeocodeService::class);
        $this->eventDispatcher = GeneralUtility::makeInstance(EventDispatcherInterface::class);
        $this->cache = GeneralUtility::getContainer()->get('cache.store_finder.middleware_cache');
    }

    protected function prepareConstraint(ServerRequestInterface $request, array $settings): Constraint
    {
        /** @var Constraint $constraint */
        $constraint = GeneralUtility::makeInstance(Constraint::class);
        $post = $request->getParsedBody();

        if (!empty($post['address'])) {
            if ((int)$settings['country'] ?? false) {
                /** @var CountryRepository $countryRepository */
                $countryRepository = GeneralUtility::makeInstance(CountryRepository::class);
                $country = $countryRepository->findByUid((int)$settings['country']);
                $constraint->setCountry($country);
            }

            if ((int)$settings['state'] ?? false) {
                /** @var CountryZoneRepository $countryZoneRepository */
                $countryZoneRepository = GeneralUtility::makeInstance(CountryZoneRepository::class);
                $countryZone = $countryZoneRepository->findByUid((int)$settings['state']);
                $constraint->setState($countryZone);
            }

            $constraint->setCity($post['address']);
            $constraint->setZipcode($post['address']);
        }

        if (!empty($post['search'])) {
            $constraint->setSearch($post['search']);
        }

        if (!empty($post['categories'])) {
            $constraint->setCategory(GeneralUtility::intExplode(',', $post['categories'], true));
        } else {
            $constraint->setCategory(GeneralUtility::intExplode(',', $settings['categories'], true));
        }

        if ($constraint->getCountry()) {
            $this->geocodeService->setSettings($settings);
            /** @var Constraint $constraint */
            $constraint = $this->geocodeService->geocodeAddress($constraint);
        }

        return $constraint;
    }

    protected function convertLocationsForResult(
        array $locations,
        array $settings,
        ServerRequestInterface $request
    ): array {
        $table = 'tx_storefinder_domain_model_location';
        /** @var ContentObjectRenderer $contentObject */
        $contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $contentObject->setRequest($request);

        foreach ($locations as &$location) {
            if ($location['categories'] ?? false) {
                $location['categories'] = GeneralUtility::intExplode(',', $location['categories'], true);
            }
            if ($location['notes'] ?? false) {
                $contentObject->start($location, $table);
                $location['notes'] = $contentObject->parseFunc(
                    $location['notes'],
                    null,
                    '< ' . $settings['tables'][$table]['parseFuncTSPath']
                );
            }
            if ($location['image'] ?? false) {
                $location['image'] = $this->getCroppedFile($location, 'image', $table, $settings);
            }
            if ($location['icon'] ?? false) {
                $location['icon'] = $this->getCroppedFile($location, 'icon', $table, $settings);
            }
        }

        return $locations;
    }

    protected function getCroppedFile(array $location, string $fieldName, string $tableName, array $settings): string
    {
        /** @var FileRepository $fileRepository */
        $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
        $file = $fileRepository->findByRelation($tableName, $fieldName, $location['uid'])[0] ?? null;

        if (!empty($settings['tables'][$tableName]['cropVariant'][$fieldName])) {
            $cropVariant = $settings['tables'][$tableName]['cropVariant'][$fieldName];
            $cropString = $file instanceof FileReference ? $file->getProperty('crop') : '';
            $cropArea = CropVariantCollection::create((string)$cropString)->getCropArea($cropVariant);
            $processingInstructions = [];
            $processingInstructions = array_merge(
                $processingInstructions,
                [
                    'crop' => $cropArea->isEmpty() ? null : $cropArea->makeAbsoluteBasedOnFile($file),
                ]
            );
        }

        if (!empty($processingInstructions) && !($file instanceof ProcessedFile)) {
            if (is_callable([$file, 'getOriginalFile'])) {
                // Get the original file from the file reference
                $file = $file->getOriginalFile();
            }
            $file = $file->process(ProcessedFile::CONTEXT_IMAGECROPSCALEMASK, $processingInstructions);
        }

        return GeneralUtility::locationHeaderUrl($file->getPublicUrl());
    }
}
