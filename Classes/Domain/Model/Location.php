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

class Location extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected $objectManager;

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
     * @var string
     */
    protected $icon = '';

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
     * @var integer
     */
    protected $center = 0;

    /**
     * @var integer
     */
    protected $zoom = 1;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Evoweb\StoreFinder\Domain\Model\Attribute>
     * @lazy
     */
    protected $attributes;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Evoweb\StoreFinder\Domain\Model\Category>
     * @lazy
     */
    protected $categories;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Evoweb\StoreFinder\Domain\Model\Location>
     * @lazy
     */
    protected $content;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Evoweb\StoreFinder\Domain\Model\Location>
     * @lazy
     */
    protected $related;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     * @lazy
     */
    protected $image;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     * @lazy
     */
    protected $media;

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
     * @lazy
     */
    protected $state = '';

    /**
     * @var double
     */
    protected $distance = 0.0;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->attributes =
            $this->categories =
            $this->content =
            $this->related =
            $this->image =
            $this->media = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    /**
     * @return \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected function getObjectManager()
    {
        if (is_null($this->objectManager)) {
            $this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                \TYPO3\CMS\Extbase\Object\ObjectManager::class
            );
        }
        return $this->objectManager;
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
        return $this->escapeJsonString($this->address);
    }

    public function setAddress(string $address)
    {
        $this->address = $address;
    }

    /**
     * @return Attribute[]|\TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function getAttributes()
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
    public function getCategories()
    {
        return $this->categories;
    }

    public function setCategories(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $categories)
    {
        $this->categories = $categories;
    }

    public function getCity(): string
    {
        return $this->escapeJsonString($this->city);
    }

    public function getCityRaw(): string
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

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content)
    {
        $this->content = $content;
    }

    public function getCountryName(): string
    {
        return $this->getCountry() ? $this->getCountry()->getShortNameEn() : '';
    }

    /**
     * @return \SJBR\StaticInfoTables\Domain\Model\Country
     */
    public function getCountry()
    {
        if (is_null($this->_country)) {
            /** @var \Evoweb\StoreFinder\Domain\Repository\CountryRepository $repository */
            $repository = $this->getObjectManager()->get(
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

    public function setCountry(\SJBR\StaticInfoTables\Domain\Model\Country $country)
    {
        $this->_country = $country;
        $this->country = $country->getUid();
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
        return $this->escapeJsonString($this->hours);
    }

    public function setHours(string $hours)
    {
        $this->hours = $hours;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function setIcon(string $icon)
    {
        $this->icon = $icon;
    }

    /**
     * @return \TYPO3\CMS\Extbase\Domain\Model\FileReference[]|\TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function getImage()
    {
        return $this->image;
    }

    public function setImage(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $image)
    {
        $this->image = $image;
    }

    /**
     * @return \TYPO3\CMS\Extbase\Domain\Model\FileReference[]|\TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function getMedia()
    {
        return $this->media;
    }

    public function setMedia(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $media)
    {
        $this->media = $media;
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
        return $this->escapeJsonString($this->notes);
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

    /**
     * @return Location[]|\TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function getRelated()
    {
        return $this->related;
    }

    public function setRelated(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $related)
    {
        $this->related = $related;
    }

    public function getStateName(): string
    {
        return $this->getState() ? $this->getState()->getNameEn() : '';
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
        return $this->escapeJsonString($this->name);
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

    public function setLatitude(float $latitude)
    {
        $this->latitude = $latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude)
    {
        $this->longitude = $longitude;
    }

    public function isGeocoded(): bool
    {
        return $this->getLatitude() && $this->getLongitude() && !$this->getGeocode();
    }

    protected function escapeJsonString(string $value): string
    {
        $escapers = ['\\', '/', '"', "\n", "\r", "\t", "\x08", "\x0c", "'"];
        $replacements = ['\\\\', '\\/', '\\"', "\\n", "\\r", "\\t", "\\f", '\\b', "\'"];
        $result = str_replace($escapers, $replacements, $value);

        return $result;
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
