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

namespace Evoweb\StoreFinder\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class MinifyViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var bool
     */
    protected $escapeChildren = false;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    public function initializeArguments(): void
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
        $content = preg_replace('@(/\*(?:[^*]|\*+[^*/])*\*+/|//.*)@', '', $content);
        $content = str_replace("\xff", '://', $content);

        /* remove tabs, spaces, newlines, etc. */
        $content = str_replace(
            [CRLF, CR, LF, "\t", '     ', '    ', '  ', '": "'],
            ['', '', '', '', '', '', '', '":"'],
            $content
        );

        /* remove other spaces before/after ) */
        return preg_replace(['(( )+\))', '(\)( )+)'], ')', $content);
    }
}
