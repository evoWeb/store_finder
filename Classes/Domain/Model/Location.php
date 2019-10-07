<?php
declare(strict_types = 1);
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

use TYPO3\CMS\Extbase\Domain\Model\FileReference;

class Location extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $storeid = '';

    /**
     * @var string
     */
    protected $address = '';

    /**
     * @var string
     */
    protected $additionaladdress = '';

    /**
     * @var string
     */
    protected $city = '';

    /**
     * @var string
     */
    protected $person = '';

    /**
     * @var string
     */
    protected $zipcode = '';

    /**
     * @var string
     */
    protected $products = '';

    /**
     * @var string
     */
    protected $email = '';

    /**
     * @var string
     */
    protected $phone = '';

    /**
     * @var string
     */
    protected $mobile = '';

    /**
     * @var string
     */
    protected $fax = '';

    /**
     * @var string
     */
    protected $hours = '';

    /**
     * @var string
     */
    protected $url = '';

    /**
     * @var string
     */
    protected $notes = '';

    /**
     * @var double
     */
    protected $latitude = 0.0000000;

    /**
     * @var double
     */
    protected $longitude = 0.0000000;

    /**
     * @var integer
     */
    protected $geocode = 0;

    /**
     * @var bool
     */
    protected $center = false;

    /**
     * @var integer
     */
    protected $zoom = 0;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Evoweb\StoreFinder\Domain\Model\Attribute>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $attributes;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Evoweb\StoreFinder\Domain\Model\Category>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $categories;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Evoweb\StoreFinder\Domain\Model\Content>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $content;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Evoweb\StoreFinder\Domain\Model\Location>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $related;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $image;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $media;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $icon;

    /**
     * @var string
     */
    protected $country = '';

    /**
     * @var \SJBR\StaticInfoTables\Domain\Model\Country
     */
    protected $_country;

    /**
     * @var \SJBR\StaticInfoTables\Domain\Model\CountryZone
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $state;

    /**
     * @var double
     */
    protected $distance = 0.0;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->initializeObject();
    }

    public function initializeObject()
    {
        $this->attributes = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->categories = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->content = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->related = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->image = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->media = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->icon = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    /**
     * @return Attribute[]|\TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function getAttributes(): \TYPO3\CMS\Extbase\Persistence\ObjectStorage
    {
        return $this->attributes;
    }

    public function setAttributes(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return Category[]|\TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function getCategories(): \TYPO3\CMS\Extbase\Persistence\ObjectStorage
    {
        return $this->categories;
    }

    public function setCategories(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $categories)
    {
        $this->categories = $categories;
    }

    /**
     * @return Content[]|\TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function getContent(): \TYPO3\CMS\Extbase\Persistence\ObjectStorage
    {
        return $this->content;
    }

    public function setContent(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $content)
    {
        $this->content = $content;
    }

    /**
     * @return Location[]|\TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function getRelated(): \TYPO3\CMS\Extbase\Persistence\ObjectStorage
    {
        return $this->related;
    }

    public function setRelated(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $related)
    {
        $this->related = $related;
    }

    /**
     * @return FileReference[]|\TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function getIcon(): \TYPO3\CMS\Extbase\Persistence\ObjectStorage
    {
        return $this->icon;
    }

    public function setIcon(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $icon)
    {
        $this->icon = $icon;
    }

    /**
     * @return FileReference[]|\TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function getImage(): \TYPO3\CMS\Extbase\Persistence\ObjectStorage
    {
        return $this->image;
    }

    public function setImage(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $image)
    {
        $this->image = $image;
    }

    /**
     * @return FileReference[]|\TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function getMedia(): \TYPO3\CMS\Extbase\Persistence\ObjectStorage
    {
        return $this->media;
    }

    public function setMedia(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $media)
    {
        $this->media = $media;
    }

    /**
     * @return \SJBR\StaticInfoTables\Domain\Model\CountryZone
     */
    public function getState()
    {
        if ($this->state instanceof \TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy) {
            $this->state = $this->state->_loadRealInstance();
        }
        return $this->state;
    }

    public function setState(\SJBR\StaticInfoTables\Domain\Model\CountryZone $state)
    {
        $this->state = $state;
    }

    public function getStateName(): string
    {
        return $this->getState() ? $this->getState()->getNameEn() : '';
    }

    /**
     * @return \SJBR\StaticInfoTables\Domain\Model\Country
     */
    public function getCountry()
    {
        if (is_null($this->_country) && $this->country) {
            /** @var \Evoweb\StoreFinder\Domain\Repository\CountryRepository $repository */
            $repository = $this->objectManager->get(
                \Evoweb\StoreFinder\Domain\Repository\CountryRepository::class
            );

            if (is_numeric($this->country)) {
                $this->_country = $repository->findByUid($this->country);
            } else {
                $this->_country = $repository->findByIsoCodeA2([$this->country])->getFirst();
            }
        }
        return $this->_country;
    }

    public function setCountry($country)
    {
        if ($country instanceof \SJBR\StaticInfoTables\Domain\Model\Country) {
            $this->_country = $country;
            $this->country = $country->getUid();
        } else {
            $this->country = $country;
        }
    }

    public function getCountryName(): string
    {
        return $this->getCountry() ? $this->getCountry()->getShortNameEn() : '';
    }

    public function getAdditionaladdress(): string
    {
        return $this->additionaladdress;
    }

    public function setAdditionaladdress(string $additionaladdress)
    {
        $this->additionaladdress = $additionaladdress;
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
