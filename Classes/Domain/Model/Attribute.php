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
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;

class Attribute extends AbstractEntity
{
    /**
     * Icon
     *
     * @var ?FileReference
     * @Lazy
     */
    protected $icon;

    protected string $name;

    protected string $description;

    protected string $cssClass = '';

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

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getCssClass(): string
    {
        return $this->cssClass;
    }

    public function setCssClass(string $cssClass)
    {
        $this->cssClass = $cssClass;
    }
}
