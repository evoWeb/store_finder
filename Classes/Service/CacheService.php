<?php

declare(strict_types=1);

namespace Evoweb\StoreFinder\Service;

/*
 * This file is part of the package t3g/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Evoweb\StoreFinder\Domain\Model\Location;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class CacheService
{
    public function __construct(readonly private CacheManager $cacheManager)
    {
    }

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
