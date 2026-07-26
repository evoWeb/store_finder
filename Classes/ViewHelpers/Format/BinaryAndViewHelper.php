<?php

declare(strict_types=1);

/*
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Evoweb\StoreFinder\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class BinaryAndViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();

        $this->registerArgument(
            'base',
            'int',
            'Content to be added on top'
        );
        $this->registerArgument(
            'content',
            'int',
            'Base to add',
            false,
            0
        );
    }

    /**
     * Make a binary addition and return the result
     */
    public function render(): int
    {
        /** @var int $content */
        $content = $this->arguments['content'];
        /** @var int $base */
        $base = $this->arguments['base'];
        if (!$content) {
            /** @var int $content */
            $content = $this->renderChildren();
        }
        return $content & $base;
    }
}
