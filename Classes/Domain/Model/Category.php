<?php
namespace Evoweb\StoreFinder\Domain\Model;

/***************************************************************
 * Copyright notice
 *
 * (c) 2013 Sebastian Fischer <typo3@evoweb.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class Category
 *
 * @package Evoweb\StoreFinder\Domain\Model
 */
class Category extends \TYPO3\CMS\Extbase\Domain\Model\Category
{
    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Evoweb\StoreFinder\Domain\Model\Category>
     * @lazy
     */
    protected $children;

    /**
     * Initialize categories, attributed and media relation
     */
    public function __construct()
    {
        $this->children = new ObjectStorage();
    }

    /**
     * Setter
     *
     * @param ObjectStorage $children
     *
     * @return void
     */
    public function setChildren($children)
    {
        $this->children = $children;
    }

    /**
     * Getter
     *
     * @return ObjectStorage
     */
    public function getChildren()
    {
        return $this->children;
    }
}
