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

class Constraint extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $address = '';

    /**
     * @var string
     */
    protected $zipcode = '';

    /**
     * @var string
     */
    protected $city = '';

    /**
     * @var string
     */
    protected $state = '';

    /**
     * @var string
     */
    protected $country = '';

    /**
     * @var string
     */
    protected $latitude = 0.0000000;

    /**
     * @var string
     */
    protected $longitude = 0.0000000;

    /**
     * @var string
     */
    protected $products = '';

    /**
     * @var array
     */
    protected $category = [];

    /**
     * @var int
     */
    protected $radius = '';

    /**
     * @var int
     */
    protected $zoom = 1;

    /**
     * @var int
     */
    protected $geocode = 0;

    /**
     * @var int
     */
    protected $limit = 0;

    /**
     * @var int
     */
    protected $page = 0;

    public function setAddress(string $address)
    {
        $this->address = $address;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setCategory(array $category)
    {
        $this->category = (array) $category;
    }

    public function getCategory(): array
    {
        return $this->category;
    }

    public function setCity(string $city)
    {
        $this->city = $city;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setState(string $state)
    {
        $this->state = $state;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setCountry(string $country)
    {
        $this->country = $country;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setLatitude(string $latitude)
    {
        $this->latitude = $latitude;
    }

    public function getLatitude(): string
    {
        return $this->latitude ?: '';
    }

    public function setLongitude(string $longitude)
    {
        $this->longitude = $longitude;
    }

    public function getLongitude(): string
    {
        return $this->longitude ?: '';
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setProducts(string $products)
    {
        $this->products = $products;
    }

    public function getProducts(): string
    {
        return $this->products;
    }

    public function setRadius(int $radius)
    {
        $this->radius = $radius;
    }

    public function getRadius(): int
    {
        return $this->radius;
    }

    public function setZipcode(string $zipcode)
    {
        $this->zipcode = $zipcode;
    }

    public function getZipcode(): string
    {
        return $this->zipcode;
    }

    public function setZoom(int $zoom)
    {
        $this->zoom = $zoom;
    }

    public function getZoom(): int
    {
        return $this->zoom;
    }

    public function setGeocode(int $geocode)
    {
        $this->geocode = $geocode;
    }

    public function getGeocode(): int
    {
        return $this->geocode;
    }

    public function setLimit(int $limit)
    {
        $this->limit = $limit;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setPage(int $page)
    {
        $this->page = $page;
    }

    public function getPage(): int
    {
        return $this->page;
    }


    public function isGeocoded(): bool
    {
        return $this->getLatitude() && $this->getLongitude() && !$this->getGeocode();
    }
}
