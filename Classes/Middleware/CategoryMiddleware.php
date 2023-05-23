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

use Evoweb\StoreFinder\Cache\MiddlewareCache;
use Evoweb\StoreFinder\Event\ModifyCategoriesMiddlewareOutputEvent;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Service\FlexFormService;

class CategoryMiddleware implements MiddlewareInterface
{
    /**
     * @var ConnectionPool
     */
    protected $connectionPool;

    /**
     * @var JsonResponse
     */
    protected $jsonResponse;

    /**
     * @var FlexFormService
     */
    protected $flexFormService;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var MiddlewareCache
     */
    protected $cachingService;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    public function __construct(
        ConnectionPool $connectionPool,
        JsonResponse $jsonResponse,
        FlexFormService $flexFormService,
        MiddlewareCache $cachingService,
        EventDispatcher $eventDispatcher
    ) {
        $this->connectionPool = $connectionPool;
        $this->jsonResponse = $jsonResponse;
        $this->flexFormService = $flexFormService;
        $this->cachingService = $cachingService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $queryParams = $request->getQueryParams();

        if (isset($queryParams['action'])
            && !empty($queryParams['action'])
            && $queryParams['action'] == 'categories'
        ) {
            $mapPluginId = (int)$queryParams['pluginUid'];
            
            $cacheIdentifier = md5(
                serialize(
                    $this->settings['settings']['categories'] ?? 'noActiveCategoriesCacheIdentifier'
                )
            );

            if ($this->cachingService->readCache($cacheIdentifier)) {
                $categories = $this->cachingService->readCache($cacheIdentifier);
            } else {
                $this->settings = $this->getPluginSettingsByPluginUid($mapPluginId);
                $categories = $this->getCategories();
                $this->cachingService->writeCache($cacheIdentifier, $categories);
            }

            $eventResult = $this->eventDispatcher->dispatch(
                new ModifyCategoriesMiddlewareOutputEvent($this, $categories, $request)
            );

            $this->jsonResponse->setPayload(
                $eventResult->getCategories()
            );

            return $this->jsonResponse;
        }

        return $handler->handle($request);
    }

    protected function getCategories(): array
    {
        $queryBuilder = $this->initializeQueryBuilder('sys_category');

        $categories = $queryBuilder
            ->select('*')
            ->from('sys_category', '')
            ->where(
                $queryBuilder->expr()->in('uid', $this->settings['settings']['categories'])
            )
            ->execute()
            ->fetchAllAssociative();

        foreach ($categories as &$category) {
            if (in_array($category['uid'], explode(',', $this->settings['settings']['activeCategories']))) {
                $category['active'] = 1;
            }
            
            if ($category['children'] > 0) {
                $category['children'] = $this->findCategoryChildrenByParentUid($category['uid']);
            }
        }

        return $categories;
    }

    protected function findCategoryChildrenByParentUid(int $parentUid): array
    {
        $queryBuilder = $this->initializeQueryBuilder('sys_category');

        $categoryChildren = $queryBuilder
            ->select('*')
            ->from('sys_category')
            ->where(
                $queryBuilder->expr()->eq('parent', $queryBuilder->createNamedParameter($parentUid))
            )
            ->execute()
            ->fetchAllAssociative();

        foreach ($categoryChildren as &$categoryChild) {
            if (in_array($categoryChild['uid'], explode(',', $this->settings['settings']['activeCategories']))) {
                $categoryChild['active'] = 1;
            }

            if ($categoryChild['children'] > 0) {
                $categoryChild['children'] = $this->findCategoryChildrenByParentUid($categoryChild['uid']);
            }
        }

        return $categoryChildren;
    }

    protected function getPluginSettingsByPluginUid(int $pluginUid): array
    {
        $queryBuilder = $this->initializeQueryBuilder('tt_content');

        $piFlexFormData = $queryBuilder
            ->select('pi_flexform')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($pluginUid))
            )
            ->execute()
            ->fetchAllAssociative();

        return $this->flexFormService->convertFlexFormContentToArray($piFlexFormData[0]['pi_flexform'] ?? '');
    }

    protected function initializeQueryBuilder(string $table): QueryBuilder
    {
        $queryBuilder = $this->connectionPool
            ->getQueryBuilderForTable($table);

        return $queryBuilder;
    }
}
