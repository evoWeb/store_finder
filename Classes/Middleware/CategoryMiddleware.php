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

class CategoryMiddleware implements MiddlewareInterface
{
    public function __construct(
        protected ContentRepository $contentRepository,
        protected CategoryRepository $categoryRepository,
        protected EventDispatcherInterface $eventDispatcher,
        protected FrontendInterface $cache,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = ltrim($request->getUri()->getPath(), '/');
        if ($path !== 'api/storefinder/categories') {
            return $handler->handle($request);
        }

        $contentUid = $request->getQueryParams()['contentUid'] ?? 0;
        $cacheIdentifier = md5('store_finder' . ($contentUid ?? 'noActiveCategoriesCacheIdentifier'));

        if ($this->cache->has($cacheIdentifier)) {
            $categories = $this->cache->get($cacheIdentifier);
        } else {
            $settings = $this->contentRepository->getPluginSettingsByPluginUid((int)$contentUid);
            $categories = $this
                ->categoryRepository
                ->getCategories($this->settings['categories'] ?? [], $settings);
            $this->cache->set($cacheIdentifier, $categories);
        }

        $eventResult = $this->eventDispatcher->dispatch(
            new ModifyCategoriesMiddlewareOutputEvent($this, $request, $categories)
        );

        return new JsonResponse($eventResult->getCategories());
    }
}
