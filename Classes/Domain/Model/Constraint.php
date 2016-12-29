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

/**
 * Class Constraint
 *
 * @package Evoweb\StoreFinder\Domain\Model
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
    protected $category = array();

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

    /**
     * Setter
     *
     * @param string $address
     *
     * @return void
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Setter
     *
     * @param array $category
     *
     * @return void
     */
    public function setCategory($category)
    {
        $this->category = (array) $category;
    }

    /**
     * Getter
     *
     * @return array
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Setter
     *
     * @param string $city
     *
     * @return void
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Setter
     *
     * @param string $state
     *
     * @return void
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Setter
     *
     * @param string $country
     *
     * @return void
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Setter
     *
     * @param string $latitude
     *
     * @return void
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude ?: '';
    }

    /**
     * Setter
     *
     * @param string $longitude
     *
     * @return void
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude ?: '';
    }

    /**
     * Setter
     *
     * @param string $name
     *
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Setter
     *
     * @param string $products
     *
     * @return void
     */
    public function setProducts($products)
    {
        $this->products = $products;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Setter
     *
     * @param int $radius
     *
     * @return void
     */
    public function setRadius($radius)
    {
        $this->radius = (int) $radius;
    }

    /**
     * Getter
     *
     * @return int
     */
    public function getRadius()
    {
        return (int) $this->radius;
    }

    /**
     * Setter
     *
     * @param string $zipcode
     *
     * @return void
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;
    }

    /**
     * Getter
     *
     * @return string
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * Setter
     *
     * @param integer $zoom
     *
     * @return void
     */
    public function setZoom($zoom)
    {
        $this->zoom = $zoom;
    }

    /**
     * Getter
     *
     * @return integer
     */
    public function getZoom()
    {
        return (int) $this->zoom;
    }

    /**
     * Setter
     *
     * @param int $geocode
     *
     * @return void
     */
    public function setGeocode($geocode)
    {
        $this->geocode = $geocode;
    }

    /**
     * Getter
     *
     * @return int
     */
    public function getGeocode()
    {
        return $this->geocode;
    }

    /**
     * Setter
     *
     * @param int $limit
     *
     * @return void
     */
    public function setLimit($limit)
    {
        $this->limit = (int) $limit;
    }

    /**
     * Getter
     *
     * @return int
     */
    public function getLimit()
    {
        return (int) $this->limit;
    }

    /**
     * Setter
     *
     * @param int $page
     *
     * @return void
     */
    public function setPage($page)
    {
        $this->page = (int) $page;
    }

    /**
     * Getter
     *
     * @return int
     */
    public function getPage()
    {
        return (int) $this->page;
    }


    /**
     * Check if latitude and longitude are set
     *
     * @return bool
     */
    public function isGeocoded()
    {
        return $this->getLatitude() && $this->getLongitude() && !$this->getGeocode();
    }
}
