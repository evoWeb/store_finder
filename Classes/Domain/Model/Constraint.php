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

class Constraint extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {
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
	 * @var string
	 */
	protected $category = '';

	/**
	 * @var integer
	 */
	protected $radius = '';

	/**
	 * @var integer
	 */
	protected $geocode = 0;

	/**
	 * @param string $address
	 */
	public function setAddress($address) {
		$this->address = $address;
	}

	/**
	 * @return string
	 */
	public function getAddress() {
		return $this->address;
	}

	/**
	 * @param string $category
	 */
	public function setCategory($category) {
		$this->category = $category;
	}

	/**
	 * @return string
	 */
	public function getCategory() {
		return $this->category;
	}

	/**
	 * @param string $city
	 */
	public function setCity($city) {
		$this->city = $city;
	}

	/**
	 * @return string
	 */
	public function getCity() {
		return $this->city;
	}

	/**
	 * @param string $state
	 */
	public function setState($state) {
		$this->state = $state;
	}

	/**
	 * @return string
	 */
	public function getState() {
		return $this->state;
	}

	/**
	 * @param string $country
	 */
	public function setCountry($country) {
		$this->country = $country;
	}

	/**
	 * @return string
	 */
	public function getCountry() {
		return $this->country;
	}

	/**
	 * @param string $latitude
	 */
	public function setLatitude($latitude) {
		$this->latitude = $latitude;
	}

	/**
	 * @return string
	 */
	public function getLatitude() {
		return $this->latitude;
	}

	/**
	 * @param string $longitude
	 */
	public function setLongitude($longitude) {
		$this->longitude = $longitude;
	}

	/**
	 * @return string
	 */
	public function getLongitude() {
		return $this->longitude;
	}

	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param string $products
	 */
	public function setProducts($products) {
		$this->products = $products;
	}

	/**
	 * @return string
	 */
	public function getProducts() {
		return $this->products;
	}

	/**
	 * @param int $radius
	 */
	public function setRadius($radius) {
		$this->radius = $radius;
	}

	/**
	 * @return int
	 */
	public function getRadius() {
		return $this->radius;
	}

	/**
	 * @param string $zipcode
	 */
	public function setZipcode($zipcode) {
		$this->zipcode = $zipcode;
	}

	/**
	 * @return string
	 */
	public function getZipcode() {
		return $this->zipcode;
	}

	/**
	 * @param int $geocode
	 */
	public function setGeocode($geocode) {
		$this->geocode = $geocode;
	}

	/**
	 * @return int
	 */
	public function getGeocode() {
		return $this->geocode;
	}
}

?>