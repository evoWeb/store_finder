<?php
namespace Evoweb\StoreFinder\ViewHelpers;

/**
 * This file is developed by evoweb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class MinifyViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var boolean
     */
    protected $escapeChildren = false;

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    public function initializeArguments()
    {
        parent::initializeArguments();

        $this->registerArgument(
            'content',
            'string',
            'Content to minify',
            false,
            ''
        );
    }

    /**
     * Renders the content minified
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): string {
        $content = $arguments['content'];
        $content = $content ?: $renderChildrenClosure();

        /* remove comments */
        $content = str_replace('://', "\xff", $content);
        $content = preg_replace('@((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))@', '', $content);
        $content = str_replace("\xff", '://', $content);

        /* remove tabs, spaces, newlines, etc. */
        $content = str_replace(
            [CRLF, CR, LF, "\t", '     ', '    ', '  ', ': '],
            ['', '', '', '', '', '', '', ':'],
            $content
        );
        /* remove other spaces before/after ) */
        $content = preg_replace(['(( )+\))', '(\)( )+)'], ')', $content);

        return $content;
    }
}
