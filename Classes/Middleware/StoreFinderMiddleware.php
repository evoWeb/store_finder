<?php

declare(strict_types=1);

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

namespace Evoweb\StoreFinder\Middleware;

use Doctrine\DBAL\Exception;
use Evoweb\StoreFinder\Domain\Model\Constraint;
use Evoweb\StoreFinder\Domain\Repository\CategoryRepository;
use Evoweb\StoreFinder\Domain\Repository\ContentRepository;
use Evoweb\StoreFinder\Domain\Repository\LocationRepository;
use Evoweb\StoreFinder\Middleware\Event\ModifyMiddlewareCategoriesEvent;
use Evoweb\StoreFinder\Middleware\Event\ModifyMiddlewareLocationsEvent;
use Evoweb\StoreFinder\Service\GeocodeService;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SJBR\StaticInfoTables\Domain\Model\CountryZone;
use SJBR\StaticInfoTables\Domain\Repository\CountryZoneRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\Lazy;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Cache\Frontend\PhpFrontend;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Country\CountryProvider;
use TYPO3\CMS\Core\Error\Http\StatusException;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScriptFactory;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Page\PageInformation;
use TYPO3\CMS\Frontend\Page\PageInformationCreationFailedException;
use TYPO3\CMS\Frontend\Page\PageInformationFactory;

final readonly class StoreFinderMiddleware implements MiddlewareInterface
{
    public function __construct(
        #[Lazy]
        private EventDispatcherInterface $eventDispatcher,
        #[Lazy]
        private CacheManager $cacheManager,
        #[Lazy]
        private ContentRepository $contentRepository,
        #[Lazy]
        private FlexFormService $flexFormService,
        #[Lazy]
        private TypoScriptService $typoScriptService,

        private FrontendTypoScriptFactory $frontendTypoScriptFactory,
        private PageInformationFactory $pageInformationFactory,
        #[Autowire(service: 'cache.typoscript')]
        private PhpFrontend $typoScriptCache,
    ) {}

    /**
     * @throws PageInformationCreationFailedException
     * @throws StatusException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->processMiddleware($request)) {
            return $handler->handle($request);
        }

        $request->getBody()->rewind();
        $json = $request->getBody()->getContents();
        /** @var SiteLanguage $requestLanguage */
        $requestLanguage = $request->getAttribute('language');
        $locale = (string)$requestLanguage->getLocale();
        $action = $request->getQueryParams()['action'] ?? '';
        $contentUid = (int)($request->getQueryParams()['contentUid'] ?? 0);

        $cacheIdentifier = $this->getCacheIdentifier($json, $locale, $action, $contentUid);
        $cache = $this->getCache();
        if ($cache && $cache->has($cacheIdentifier)) {
            $rows = $cache->get($cacheIdentifier);
        } else {
            [$settings, $request] = $this->getSettings($request, $contentUid);
            $rows = $this->{$action . 'Action'}($request, $settings);
            $cache?->set($cacheIdentifier, $rows);
        }

        return new JsonResponse($rows);
    }

    protected function getCache(): ?FrontendInterface
    {
        try {
            $cache = $this->cacheManager->getCache('store_finder_middleware_cache') ?? null;
        } catch (NoSuchCacheException) {
            $cache = null;
        }
        return $cache;
    }

    protected function processMiddleware(ServerRequestInterface $request): bool
    {
        return str_contains($request->getUri()->getPath(), 'api/storefinder/')
            && in_array(($request->getQueryParams()['action'] ?? ''), ['locations', 'categories']);
    }

    protected function getCacheIdentifier(string $json, string $locale, string $action, int $contentUid): string
    {
        $encryptionKey = $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] ?? '';
        return sha1($encryptionKey . 'store_finder' . $json . $locale . $action . $contentUid);
    }

    /**
     * @return array<array<string, mixed>|ServerRequestInterface>
     * @throws PageInformationCreationFailedException
     * @throws StatusException
     */
    protected function getSettings(ServerRequestInterface $request, int $contentUid): array
    {
        $pageId = $request->getAttribute('routing')->getPageId();
        $contentSettings = $this->getContentSettings($contentUid);

        if ($pageId === $contentSettings['pid']) {
            $typoScript = $request->getAttribute('frontend.typoscript');
        } else {
            $pageInformation = $this->getPageInformation($request, $contentSettings['pid']);
            $typoScript = $this->getTypoScriptForPage($request, $pageInformation);
        }

        $settings = $this->typoScriptService->convertTypoScriptArrayToPlainArray(
            $typoScript->getSetupArray()['plugin.']['tx_storefinder.']['ajax.'] ?? [],
        ) + $contentSettings;

        return [$settings, $request];
    }

    /**
     * @throws PageInformationCreationFailedException
     * @throws StatusException
     */
    protected function getPageInformation(ServerRequestInterface $request, int $pageUid): PageInformation
    {
        $request = $request->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $request = $request->withAttribute('routing', new PageArguments($pageUid, '0', []));
        return $this->pageInformationFactory->create($request);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getContentSettings(int $contentUid): array
    {
        $row = $this->contentRepository->findByUid($contentUid);

        $settings = $this->flexFormService->convertFlexFormContentToArray($row['pi_flexform'] ?? '')['settings'] ?? [];
        $settings['pid'] = $row['pid'];
        $settings['storagePid'] = $row['pages'];

        return $settings;
    }

    protected function getTypoScriptForPage(
        ServerRequestInterface $request,
        PageInformation $pageInformation,
    ): FrontendTypoScript {
        $site = $request->getAttribute('site');
        $pageType = $request->getAttribute('routing')->getPageType();
        $sysTemplateRows = $pageInformation->getSysTemplateRows();
        $isCachingAllowed = $request->getAttribute('frontend.cache.instruction')->isCachingAllowed();
        $conditionMatcherVariables = $this->prepareConditionMatcherVariables($request, $pageInformation);

        $frontendTypoScript = $this->frontendTypoScriptFactory->createSettingsAndSetupConditions(
            $site,
            $sysTemplateRows,
            $conditionMatcherVariables,
            $isCachingAllowed ? $this->typoScriptCache : null,
        );

        try {
            $frontendTypoScript = $this->frontendTypoScriptFactory->createSetupConfigOrFullSetup(
                true,
                $frontendTypoScript,
                $site,
                $sysTemplateRows,
                $conditionMatcherVariables,
                $pageType,
                $isCachingAllowed ? $this->typoScriptCache : null,
                $request,
            );
        } catch (\JsonException) {
        }

        return $frontendTypoScript;
    }

    /**
     * @return array<string, mixed>
     */
    private function prepareConditionMatcherVariables(
        ServerRequestInterface $request,
        PageInformation $pageInformation,
    ): array {
        $topDownRootLine = $pageInformation->getRootLine();
        $localRootline = $pageInformation->getLocalRootLine();
        ksort($topDownRootLine);
        return [
            'request' => $request,
            'pageId' => $pageInformation->getId(),
            'page' => $pageInformation->getPageRecord(),
            'fullRootLine' => $topDownRootLine,
            'localRootLine' => $localRootline,
            'site' => $request->getAttribute('site'),
            'siteLanguage' => $request->getAttribute('language'),
            'tsfe' => $request->getAttribute('frontend.controller'),
        ];
    }

    /**
     * @param array<string, mixed> $settings
     * @return array<array<string, mixed>>
     * @throws Exception
     * @throws AspectNotFoundException
     */
    protected function categoriesAction(ServerRequestInterface $request, array $settings): array
    {
        /** @var CategoryRepository $categoryRepository */
        $categoryRepository = GeneralUtility::makeInstance(CategoryRepository::class);
        $categoryRepository->setSettings($settings);

        $categories = GeneralUtility::intExplode(',', $settings['categories'] ?? '', true);
        $categoryTree = $categoryRepository->getCategoriesByParents($categories);

        $eventResult = $this->eventDispatcher->dispatch(
            new ModifyMiddlewareCategoriesEvent($request, $this, $settings, $categoryTree),
        );
        return $eventResult->getCategories();
    }

    /**
     * @param array<string, mixed> $settings
     * @return array<array<string, mixed>>
     * @throws Exception
     * @throws AspectNotFoundException
     */
    protected function locationsAction(ServerRequestInterface $request, array $settings): array
    {
        /** @var LocationRepository $locationRepository */
        $locationRepository = GeneralUtility::makeInstance(LocationRepository::class);
        $locationRepository->setSettings($settings);

        $json = $request->getBody()->getContents();
        $constraint = $this->prepareConstraint($json, $settings);
        $rows = $locationRepository->findAllForAjaxMiddleware($constraint);

        $eventResult = $this->eventDispatcher->dispatch(
            new ModifyMiddlewareLocationsEvent($request, $this, $settings, $rows),
        );
        return $eventResult->getLocations();
    }

    /**
     * @param array<string, mixed> $settings
     * @throws Exception
     */
    protected function prepareConstraint(string $json, array $settings): Constraint
    {
        /** @var Constraint $constraint */
        $constraint = GeneralUtility::makeInstance(Constraint::class);
        $post = strlen($json) > 0 ? json_decode($json, true) : [];

        if (!empty($post['address'])) {
            if ((string)($settings['country'] ?? '')) {
                /** @var CountryProvider $countryProvider */
                $countryProvider = GeneralUtility::makeInstance(CountryProvider::class);
                $country = $countryProvider->getByAlpha2IsoCode((string)$settings['country']);
                $constraint->setCountry($country);
            }

            if ((int)($settings['state'] ?? 0)) {
                /** @var CountryZoneRepository $countryZoneRepository */
                $countryZoneRepository = GeneralUtility::makeInstance(CountryZoneRepository::class);
                /** @var CountryZone $countryZone */
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
            /** @var Constraint $constraint */
            $constraint = $geocodeService->geocodeAddress($constraint);
        }

        return $constraint;
    }
}
