<?php
declare(strict_types = 1);
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

class CacheService
{
    /**
     * @param Location $location
     * @throws \InvalidArgumentException
     */
    public function addTagsForPost(Location $location): void
    {
        $this->addTagToPage('tx_storefinder_domain_model_location_' . $location->getUid());
        foreach ($location->getCategories() as $category) {
            $this->addTagToPage('tx_storefinder_domain_model_category_' . $category->getUid());
        }
    }

    /**
     * @param string $tag
     */
    public function addTagToPage(string $tag): void
    {
        $this->addTagsToPage([$tag]);
    }

    /**
     * @param array $tags
     */
    public function addTagsToPage(array $tags): void
    {
        $this->getTypoScriptFrontendController()->addCacheTags($tags);
    }

    /**
     * @param string $tag
     * @throws \InvalidArgumentException
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     */
    public function flushCacheByTag(string $tag): void
    {
        $this->flushCacheByTags([$tag]);
    }

    /**
     * @param array $tags
     * @throws \InvalidArgumentException
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     */
    public function flushCacheByTags(array $tags): void
    {
        GeneralUtility::makeInstance(CacheManager::class)
            ->getCache('cache_pages')
            ->flushByTags($tags);
    }

    /**
     * @return mixed|\TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }
}
