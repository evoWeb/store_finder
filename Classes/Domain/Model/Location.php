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

namespace Evoweb\StoreFinder\Domain\Model;

use SJBR\StaticInfoTables\Domain\Model\CountryZone;
use TYPO3\CMS\Core\Country\Country;
use TYPO3\CMS\Extbase\Annotation as Extbase;
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

    #[Extbase\ORM\Lazy]
    protected null|Country|LazyLoadingProxy $country = null;

    #[Extbase\ORM\Lazy]
    protected null|CountryZone|LazyLoadingProxy $state = null;

    /**
     * @var ObjectStorage<Attribute>
     */
    #[Extbase\ORM\Lazy]
    protected ObjectStorage $attributes;

    /**
     * @var ObjectStorage<Category>
     */
    #[Extbase\ORM\Lazy]
    protected ObjectStorage $categories;

    /**
     * @var ObjectStorage<Content>
     */
    #[Extbase\ORM\Lazy]
    protected ObjectStorage $contentElements;

    /**
     * @var ObjectStorage<Location>
     */
    #[Extbase\ORM\Lazy]
    protected ObjectStorage $related;

    /**
     * @var ObjectStorage<FileReference>
     */
    #[Extbase\ORM\Lazy]
    protected ObjectStorage $image;

    /**
     * @var ObjectStorage<FileReference>
     */
    #[Extbase\ORM\Lazy]
    protected ObjectStorage $media;

    /**
     * @var ObjectStorage<FileReference>
     */
    #[Extbase\ORM\Lazy]
    protected ObjectStorage $layer;

    /**
     * @var ObjectStorage<FileReference>
     */
    #[Extbase\ORM\Lazy]
    protected ObjectStorage $icon;

    protected float $distance = 0.0;

    public function __construct()
    {
        $this->initializeObject();
    }

    public function initializeObject(): void
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

    public function setAttributes(ObjectStorage $attributes): void
    {
        $this->attributes = $attributes;
    }

    public function getCategories(): ObjectStorage
    {
        return $this->categories;
    }

    public function setCategories(ObjectStorage $categories): void
    {
        $this->categories = $categories;
    }

    public function getContentElements(): ObjectStorage
    {
        return $this->contentElements;
    }

    public function setContentElements(ObjectStorage $contentElements): void
    {
        $this->contentElements = $contentElements;
    }

    public function getRelated(): ObjectStorage
    {
        return $this->related;
    }

    public function setRelated(ObjectStorage $related): void
    {
        $this->related = $related;
    }

    public function getIcon(): ObjectStorage
    {
        return $this->icon;
    }

    public function setIcon(ObjectStorage $icon): void
    {
        $this->icon = $icon;
    }

    public function getLayer(): ObjectStorage
    {
        return $this->layer;
    }

    public function setLayer(ObjectStorage $layer): void
    {
        $this->layer = $layer;
    }

    public function getImage(): ObjectStorage
    {
        return $this->image;
    }

    public function setImage(ObjectStorage $image): void
    {
        $this->image = $image;
    }

    public function getMedia(): ObjectStorage
    {
        return $this->media;
    }

    public function setMedia(ObjectStorage $media): void
    {
        $this->media = $media;
    }

    public function getState(): ?CountryZone
    {
        return $this->state instanceof LazyLoadingProxy
            ? $this->state->_loadRealInstance()
            : $this->state;
    }

    public function setState(?CountryZone $state): void
    {
        $this->state = $state;
    }

    public function getStateName(): string
    {
        return $this->getState() ? $this->getState()->getNameEn() : '';
    }

    public function getCountry(): ?Country
    {
        return $this->country instanceof LazyLoadingProxy
            ? $this->country->_loadRealInstance()
            : $this->country;
    }

    public function setCountry(?Country $country): void
    {
        $this->country = $country;
    }

    public function getCountryName(): string
    {
        return $this->getCountry() ? $this->getCountry()->getLocalizedNameLabel() : '';
    }

    public function getAdditionaladdress(): string
    {
        return $this->additionaladdress;
    }

    public function setAdditionaladdress(string $additionalAddress): void
    {
        $this->additionaladdress = $additionalAddress;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getPerson(): string
    {
        return $this->person;
    }

    public function setPerson(string $person): void
    {
        $this->person = $person;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getFax(): string
    {
        return $this->fax;
    }

    public function setFax(string $fax): void
    {
        $this->fax = $fax;
    }

    public function getGeocode(): int
    {
        return $this->geocode;
    }

    public function setGeocode(int $geocode): void
    {
        $this->geocode = $geocode;
    }

    public function getHours(): string
    {
        return $this->hours;
    }

    public function setHours(string $hours): void
    {
        $this->hours = $hours;
    }

    public function getMobile(): string
    {
        return $this->mobile;
    }

    public function setMobile(string $mobile): void
    {
        $this->mobile = $mobile;
    }

    public function getNotes(): string
    {
        return $this->notes;
    }

    public function setNotes(string $notes): void
    {
        $this->notes = $notes;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    public function getProducts(): string
    {
        return $this->products;
    }

    public function setProducts(string $products): void
    {
        $this->products = $products;
    }

    public function getStoreid(): string
    {
        return $this->storeid;
    }

    public function setStoreid(string $storeid): void
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

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getZipcode(): string
    {
        return $this->zipcode;
    }

    public function setZipcode(string $zipcode): void
    {
        $this->zipcode = $zipcode;
    }

    public function getCenter(): bool
    {
        return $this->center;
    }

    public function setCenter(bool $center): void
    {
        $this->center = $center;
    }

    public function getZoom(): int
    {
        return $this->zoom;
    }

    public function setZoom(int $zoom): void
    {
        $this->zoom = $zoom;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): void
    {
        $this->latitude = (float)$latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): void
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

    public function setDistance(float $distance): void
    {
        $this->distance = $distance;
    }
}
