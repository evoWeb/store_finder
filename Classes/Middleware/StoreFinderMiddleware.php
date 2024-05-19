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
use Evoweb\StoreFinder\Domain\Repository\CategoryRepository;
use Evoweb\StoreFinder\Domain\Repository\ContentRepository;
use Evoweb\StoreFinder\Domain\Repository\CountryRepository;
use Evoweb\StoreFinder\Domain\Repository\LocationRepository;
use Evoweb\StoreFinder\Event\ModifyMiddlewareCategoriesEvent;
use Evoweb\StoreFinder\Event\ModifyMiddlewareLocationsEvent;
use Evoweb\StoreFinder\Service\GeocodeService;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SJBR\StaticInfoTables\Domain\Repository\CountryZoneRepository;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class StoreFinderMiddleware implements MiddlewareInterface
{
    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
        protected CacheManager $cacheManager
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = ltrim($request->getUri()->getPath(), '/');
        $queryParams = $request->getQueryParams();
        $action = $queryParams['action'] ?? '';
        if (!str_contains($path, 'api/storefinder/') || !in_array($action, ['locations', 'categories'])) {
            return $handler->handle($request);
        }

        /** @var SiteLanguage $requestLanguage */
        $requestLanguage = $request->getAttribute('language');
        $contentUid = $queryParams['contentUid'] ?? 0;
        $cacheIdentifier = md5('store_finder' . $action . $contentUid . $requestLanguage->getLanguageId());

        $cache = $this->cacheManager->getCache('store_finder_middleware_cache');
        if (empty($request->getBody()->getContents()) && $cache->has($cacheIdentifier)) {
            $rows = $cache->get($cacheIdentifier);
        } else {
            [$settings, $request] = $this->getSettings($request, (int)$contentUid);
            $rows = $this->{$action . 'Action'}($request, $settings);
            $request->getBody()->rewind();
            if (empty($request->getBody()->getContents())) {
                $cache->set($cacheIdentifier, $rows);
            }
        }

        return new JsonResponse($rows);
    }

    protected function getSettings(ServerRequestInterface $request, int $contentUid): array
    {
        /** @var ContentRepository $contentRepository */
        $contentRepository = GeneralUtility::makeInstance(ContentRepository::class);
        /** @var FlexFormService $flexFormService */
        $flexFormService = GeneralUtility::makeInstance(FlexFormService::class);
        /** @var TypoScriptService $typoScriptService */
        $typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);

        $row = $contentRepository->findByUid($contentUid);
        $settings = $flexFormService->convertFlexFormContentToArray($row['pi_flexform'] ?? '')['settings'] ?? [];
        $settings['pid'] = $row['pid'];
        $settings['storagePid'] = $row['pages'];

        $controller = $request->getAttribute('frontend.controller');
        $controller->id = $row['pid'];
        $controller->determineId($request);
        $request = $controller->getFromCache($request);

        $typoScript = $request->getAttribute('frontend.typoscript');
        $settings += $typoScriptService->convertTypoScriptArrayToPlainArray(
            $typoScript->getSetupArray()['plugin.']['tx_storefinder.']['ajax.'] ?? []
        );

        return [$settings, $request];
    }

    protected function categoriesAction(ServerRequestInterface $request, array $settings): array
    {
        /** @var CategoryRepository $categoryRepository */
        $categoryRepository = GeneralUtility::makeInstance(CategoryRepository::class);
        $categoryRepository->setSettings($settings);

        $categories = GeneralUtility::intExplode(',', $settings['categories'] ?? '', true);
        $categoryTree = $categoryRepository->getCategoriesByParents($categories);

        $eventResult = $this->eventDispatcher->dispatch(
            new ModifyMiddlewareCategoriesEvent($request, $this, $settings, $categoryTree)
        );
        return $eventResult->getCategories();
    }

    protected function locationsAction(ServerRequestInterface $request, array $settings): array
    {
        /** @var LocationRepository $locationRepository */
        $locationRepository = GeneralUtility::makeInstance(LocationRepository::class);
        $locationRepository->setSettings($settings);

        $constraint = $this->prepareConstraint($request, $settings);
        $rows = $locationRepository->getLocations($constraint);

        $eventResult = $this->eventDispatcher->dispatch(
            new ModifyMiddlewareLocationsEvent($request, $this, $settings, $rows)
        );
        return $eventResult->getLocations();
    }

    protected function prepareConstraint(ServerRequestInterface $request, array $settings): Constraint
    {
        /** @var Constraint $constraint */
        $constraint = GeneralUtility::makeInstance(Constraint::class);
        $request->getBody()->rewind();
        $post = json_decode($request->getBody()->getContents(), true);

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
            /** @var CategoryRepository $categoryRepository */
            $categoryRepository = GeneralUtility::makeInstance(CategoryRepository::class);
            $categories = GeneralUtility::intExplode(',', $settings['categories'], true);
            $categories = $categoryRepository->enrichCategoriesWithChildren($categories);
            $constraint->setCategory($categories);
        }

        if ($constraint->getCountry()) {
            /** @var GeocodeService $geocodeService */
            $geocodeService = GeneralUtility::makeInstance(GeocodeService::class);
            $geocodeService->setSettings($settings);
            $constraint = $geocodeService->geocodeAddress($constraint);
        }

        return $constraint;
    }
}
