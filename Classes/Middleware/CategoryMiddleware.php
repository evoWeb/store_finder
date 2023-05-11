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

use Evoweb\StoreFinder\Domain\Repository\CategoryRepository;
use Evoweb\StoreFinder\Domain\Repository\ContentRepository;
use Evoweb\StoreFinder\Event\ModifyCategoriesMiddlewareOutputEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CategoryMiddleware implements MiddlewareInterface
{
    protected ContentRepository $contentRepository;

    protected CategoryRepository $categoryRepository;

    protected EventDispatcherInterface $eventDispatcher;

    protected FrontendInterface $cache;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = ltrim($request->getUri()->getPath(), '/');
        $queryParams = $request->getQueryParams();
        if (!str_starts_with($path, 'api/storefinder/') || ($queryParams['action'] ?? '') !== 'categories') {
            return $handler->handle($request);
        }
        $this->initializeObject();

        $contentUid = $queryParams['contentUid'] ?? 0;
        $cacheIdentifier = md5('store_finder' . ($contentUid ?? 'noActiveCategoriesCacheIdentifier'));

        if ($this->cache->has($cacheIdentifier)) {
            $categories = $this->cache->get($cacheIdentifier);
        } else {
            $settings = $this->contentRepository->getPluginSettingsByPluginUid((int)$contentUid);
            $this->categoryRepository->setSettings($settings);
            $categories = $this->categoryRepository->getCategories(
                GeneralUtility::intExplode(',', $settings['categories'] ?? '', true)
            );
            $this->cache->set($cacheIdentifier, $categories);
        }

        $eventResult = $this->eventDispatcher->dispatch(
            new ModifyCategoriesMiddlewareOutputEvent($this, $request, $categories)
        );

        return new JsonResponse($eventResult->getCategories());
    }

    protected function initializeObject(): void
    {
        $this->contentRepository = GeneralUtility::makeInstance(ContentRepository::class);
        $this->categoryRepository = GeneralUtility::makeInstance(CategoryRepository::class);
        $this->eventDispatcher = GeneralUtility::makeInstance(EventDispatcherInterface::class);
        $this->cache = GeneralUtility::getContainer()->get('cache.store_finder.middleware_cache');
    }
}
