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

use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;

class Attribute extends AbstractEntity
{
    /**
     * Icon
     *
     * @var ?FileReference
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $icon;

    protected string $name;

    public function getIcon(): FileReference
    {
        if ($this->icon instanceof LazyLoadingProxy) {
            $this->icon = $this->icon->_loadRealInstance();
        }
        return $this->icon;
    }

    public function setIcon(FileReference $icon)
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
