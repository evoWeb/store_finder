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
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

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

    public function addTagsToPage(array $tags): void
    {
        $this->getTypoScriptFrontendController()->addCacheTags($tags);
    }

    public function flushCacheByTag(string $tag): void
    {
        $this->flushCacheByTags([$tag]);
    }

    public function flushCacheByTags(array $tags): void
    {
        $this->cacheManager
            ->getCache('pages')
            ->flushByTags($tags);
    }

    protected function getTypoScriptFrontendController(): ?TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
