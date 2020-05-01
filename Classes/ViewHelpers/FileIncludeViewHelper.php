<?php

declare(strict_types=1);

namespace Evoweb\StoreFinder\ViewHelpers;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class FileIncludeViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{
    use CompileWithRenderStatic;

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('file', 'string', 'File to include', true);
        $this->registerArgument('type', 'string', 'File type js or css', false, 'js');
        $this->registerArgument('position', 'string', 'Include in header or footer', false, 'footer');
        $this->registerArgument('external', 'bool', 'If file is an external resource', false, false);
    }

    /**
     * Adds file to page renderer
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
        /** @var \TYPO3\CMS\Core\Page\PageRenderer $pageRenderer */
        $pageRenderer = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Page\PageRenderer::class);

        if (strtolower($arguments['type']) == 'js') {
            if (strtolower($arguments['position']) == 'footer') {
                $pageRenderer->addJsFooterFile($arguments['file'], 'text/javascript', !$arguments['external']);
            } else {
                $pageRenderer->addJsFile($arguments['file'], 'text/javascript', !$arguments['external']);
            }
        } else {
            $pageRenderer->addCssFile($arguments['file']);
        }
    }
}
