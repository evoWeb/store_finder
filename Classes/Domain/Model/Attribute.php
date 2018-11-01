<?php
namespace Evoweb\StoreFinder\Domain\Model;

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

class Attribute extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * Image
     *
     * @var \TYPO3\CMS\Extbase\Domain\Model\FileReference
     * @lazy
     */
    protected $icon;

    /**
     * @var string
     */
    protected $name;

    /**
     * @return \TYPO3\CMS\Extbase\Domain\Model\FileReference
     */
    public function getIcon()
    {
        return $this->icon;
    }

    public function setIcon(\TYPO3\CMS\Extbase\Domain\Model\FileReference $icon)
    {
        $this->icon = $icon;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }
}
