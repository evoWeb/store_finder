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

use SJBR\StaticInfoTables\Domain\Model\Country;
use SJBR\StaticInfoTables\Domain\Model\CountryZone;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class Location extends AbstractEntity
{
    protected string $name = '';

    protected string $storeid = '';

    protected string $address = '';

    protected string $additionaladdress = '';

    protected string $city = '';

    protected string $person = '';

    protected string $zipcode = '';

    protected string $products = '';

    protected string $email = '';

    protected string $phone = '';

    protected string $mobile = '';

    protected string $fax = '';

    protected string $hours = '';

    protected string $url = '';

    protected string $notes = '';

    protected float $latitude = 0.0000000;

    protected float $longitude = 0.0000000;

    protected int $geocode = 0;

    protected bool $center = false;

    protected int $zoom = 0;

    /**
     * @var ObjectStorage<Attribute>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected ObjectStorage $attributes;

    /**
     * @var ObjectStorage<Category>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected ObjectStorage $categories;

    /**
     * @var ObjectStorage<Content>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected ObjectStorage $contentElements;

    /**
     * @var ObjectStorage<Location>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected ObjectStorage $related;

    /**
     * @var ObjectStorage<FileReference>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected ObjectStorage $image;

    /**
     * @var ObjectStorage<FileReference>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected ObjectStorage $media;

    /**
     * @var ObjectStorage<FileReference>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected ObjectStorage $layer;

    /**
     * @var ObjectStorage<FileReference>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected ObjectStorage $icon;

    /**
     * @var ?Country
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $country;

    /**
     * @var ?CountryZone
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $state;

    protected float $distance = 0.0;

    public function __construct()
    {
        $this->initializeObject();
    }

    public function initializeObject()
    {
        $this->attributes = new ObjectStorage();
        $this->categories = new ObjectStorage();
        $this->contentElements = new ObjectStorage();
        $this->related = new ObjectStorage();
        $this->image = new ObjectStorage();
        $this->media = new ObjectStorage();
        $this->layer = new ObjectStorage();
        $this->icon = new ObjectStorage();
    }

    public function getAttributes(): ObjectStorage
    {
        return $this->attributes;
    }

    public function setAttributes(ObjectStorage $attributes)
    {
        $this->attributes = $attributes;
    }

    public function getCategories(): ObjectStorage
    {
        return $this->categories;
    }

    public function setCategories(ObjectStorage $categories)
    {
        $this->categories = $categories;
    }

    public function getContentElements(): ObjectStorage
    {
        return $this->contentElements;
    }

    public function setContentElements(ObjectStorage $contentElements)
    {
        $this->contentElements = $contentElements;
    }

    public function getRelated(): ObjectStorage
    {
        return $this->related;
    }

    public function setRelated(ObjectStorage $related)
    {
        $this->related = $related;
    }

    public function getIcon(): ObjectStorage
    {
        return $this->icon;
    }

    public function setIcon(ObjectStorage $icon)
    {
        $this->icon = $icon;
    }

    public function getLayer(): ObjectStorage
    {
        return $this->layer;
    }

    public function setLayer(ObjectStorage $layer)
    {
        $this->layer = $layer;
    }

    public function getImage(): ObjectStorage
    {
        return $this->image;
    }

    public function setImage(ObjectStorage $image)
    {
        $this->image = $image;
    }

    public function getMedia(): ObjectStorage
    {
        return $this->media;
    }

    public function setMedia(ObjectStorage $media)
    {
        $this->media = $media;
    }

    public function getState(): ?CountryZone
    {
        if ($this->state instanceof LazyLoadingProxy) {
            $this->state = $this->state->_loadRealInstance();
        }
        return $this->state;
    }

    public function setState(CountryZone $state)
    {
        $this->state = $state;
    }

    public function getStateName(): string
    {
        return $this->getState() ? $this->getState()->getNameEn() : '';
    }

    public function getCountry(): ?Country
    {
        if ($this->country instanceof LazyLoadingProxy) {
            $this->country = $this->country->_loadRealInstance();
        }
        return $this->country;
    }

    public function setCountry(Country $country)
    {
        $this->country = $country;
    }

    public function getCountryName(): string
    {
        return $this->getCountry() ? $this->getCountry()->getShortNameEn() : '';
    }

    public function getAdditionaladdress(): string
    {
        return $this->additionaladdress;
    }

    public function setAdditionaladdress(string $additionalAddress)
    {
        $this->additionaladdress = $additionalAddress;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address)
    {
        $this->address = $address;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city)
    {
        $this->city = $city;
    }

    public function getPerson(): string
    {
        return $this->person;
    }

    public function setPerson(string $person)
    {
        $this->person = $person;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    public function getFax(): string
    {
        return $this->fax;
    }

    public function setFax(string $fax)
    {
        $this->fax = $fax;
    }

    public function getGeocode(): int
    {
        return $this->geocode;
    }

    public function setGeocode(int $geocode)
    {
        $this->geocode = $geocode;
    }

    public function getHours(): string
    {
        return $this->hours;
    }

    public function setHours(string $hours)
    {
        $this->hours = $hours;
    }

    public function getMobile(): string
    {
        return $this->mobile;
    }

    public function setMobile(string $mobile)
    {
        $this->mobile = $mobile;
    }

    public function getNotes(): string
    {
        return $this->notes;
    }

    public function setNotes(string $notes)
    {
        $this->notes = $notes;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone)
    {
        $this->phone = $phone;
    }

    public function getProducts(): string
    {
        return $this->products;
    }

    public function setProducts(string $products)
    {
        $this->products = $products;
    }

    public function getStoreid(): string
    {
        return $this->storeid;
    }

    public function setStoreid(string $storeid)
    {
        $this->storeid = $storeid;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNameRaw(): string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url)
    {
        $this->url = $url;
    }

    public function getZipcode(): string
    {
        return $this->zipcode;
    }

    public function setZipcode(string $zipcode)
    {
        $this->zipcode = $zipcode;
    }

    public function getCenter(): bool
    {
        return $this->center;
    }

    public function setCenter(bool $center)
    {
        $this->center = $center;
    }

    public function getZoom(): int
    {
        return $this->zoom;
    }

    public function setZoom(int $zoom)
    {
        $this->zoom = $zoom;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude)
    {
        $this->latitude = (float)$latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude)
    {
        $this->longitude = (float)$longitude;
    }

    public function isGeocoded(): bool
    {
        return $this->getLatitude() && $this->getLongitude() && !$this->getGeocode();
    }

    public function getDistance(): float
    {
        return $this->distance;
    }

    public function setDistance(float $distance)
    {
        $this->distance = $distance;
    }
}
