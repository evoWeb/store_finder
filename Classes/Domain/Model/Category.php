<?php

declare(strict_types=1);

namespace Evoweb\StoreFinder\Domain\Model;

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

use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class Category extends \TYPO3\CMS\Extbase\Domain\Model\Category
{
    /**
     * @var ObjectStorage<Category>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected ObjectStorage $children;

    public function __construct()
    {
        $this->initializeObject();
    }

    public function initializeObject()
    {
        $this->children = new ObjectStorage();
    }

    public function setChildren(ObjectStorage $children)
    {
        $this->children = $children;
    }

    public function getChildren(): ?ObjectStorage
    {
        return $this->children;
    }
}
