<?php
declare(strict_types = 1);
namespace Evoweb\StoreFinder\ViewHelpers;

/*
 * This file is part of the package t3g/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Evoweb\StoreFinder\Domain\Model\Location;
use Evoweb\StoreFinder\Service\CacheService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class CacheViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('location', Location::class, 'the location to tag', true);
    }

    /**
     * Render
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $location = $arguments['location'];
        GeneralUtility::makeInstance(CacheService::class)->addTagsForPost($location);
    }
}
