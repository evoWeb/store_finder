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
use Evoweb\StoreFinder\Event\ModifyLocationsMiddlewareOutputEvent;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Http\JsonResponse;

class LocationMiddleware implements MiddlewareInterface
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
        MiddlewareCache $cachingService,
        EventDispatcher $eventDispatcher

    ) {
        $this->connectionPool = $connectionPool;
        $this->jsonResponse = $jsonResponse;
        $this->cachingService = $cachingService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $queryParams = $request->getQueryParams();

        if (isset($queryParams['action'])
            && !empty($queryParams['action'])
            && $queryParams['action'] == 'locations'
        ) {
            $cacheIdentifier = md5(serialize($queryParams ?? 'allLocationsCacheIdentifier'));

            if ($this->cachingService->readCache($cacheIdentifier)) {
                $locations = $this->cachingService->readCache($cacheIdentifier);
            } else {
                $locations = $this->getLocations();
                $this->cachingService->writeCache($cacheIdentifier, $locations);
            }

            $eventResult = $this->eventDispatcher->dispatch(
                new ModifyLocationsMiddlewareOutputEvent($this, $locations, $request)
            );

            $this->jsonResponse->setPayload(
                $eventResult->getLocations()
            );

            return $this->jsonResponse;
        }

        return $handler->handle($request);
    }

    protected function initializeQueryBuilder(string $table): QueryBuilder
    {
        $queryBuilder = $this->connectionPool
            ->getQueryBuilderForTable($table);

        return $queryBuilder;
    }

    protected function getLocations(): array
    {
        $queryBuilder = $this->initializeQueryBuilder('tx_storefinder_domain_model_location');
        $locations = $queryBuilder
            ->select('*')
            ->from('tx_storefinder_domain_model_location');

        return $locations->execute()->fetchAllAssociative();
    }
}
