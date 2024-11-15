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

namespace Evoweb\StoreFinder\Service;

use Evoweb\StoreFinder\Domain\Model\Location;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\CacheTag;

class CacheService
{
    public function __construct(readonly private CacheManager $cacheManager) {}

    public function addTagsForPost(Location $location): void
    {
        $this->addTagToPage('tx_storefinder_domain_model_location_' . $location->getUid());
        foreach ($location->getCategories() as $category) {
            $this->addTagToPage('tx_storefinder_domain_model_category_' . $category->getUid());
        }
    }

    public function addTagToPage(string $tag): void
    {
        $this->addTagsToPage([$tag]);
    }

    /**
     * @param string[] $tags
     */
    public function addTagsToPage(array $tags): void
    {
        $cacheDataCollector = $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.cache.collector');
        $cacheDataCollector->addCacheTags(...array_map(fn(string $tag) => new CacheTag($tag), $tags));
    }

    public function flushCacheByTag(string $tag): void
    {
        try {
            $this->cacheManager
                ->getCache('pages')
                ->flushByTag($tag);
        } catch (\Exception) {}
    }

    /**
     * @param string[] $tags
     */
    public function flushCacheByTags(array $tags): void
    {
        try {
            $this->cacheManager
                ->getCache('pages')
                ->flushByTags($tags);
        } catch (\Exception) {}
    }
}
