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

class Location extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {
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
	protected $useAsCenter = 0;

	/**
	 * @var integer
	 */
	protected $zoom = 1;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Evoweb\StoreFinder\Domain\Model\Attributes>
	 * @lazy
	 */
	protected $attributes = '';

	/**
	 * var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Evoweb\StoreFinder\Domain\Model\Category>
	 * @var string
	 * @lazy
	 */
	protected $categories = '';

	/**
	 * var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<Tt_Content>
	 * @var string
	 * @lazy
	 */
	protected $content = '';

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Evoweb\StoreFinder\Domain\Model\Location>
	 * @lazy
	 */
	protected $related;

	/**
	 * @todo fal
	 * \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Core\Resource\File>
	 * @var string
	 * @lazy
	 */
	protected $image = '';

	/**
	 * @todo fal
	 * \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Core\Resource\File>
	 * @var string
	 * @lazy
	 */
	protected $media = '';

	/**
	 * @var \SJBR\StaticInfoTables\Domain\Model\Country
	 * @lazy
	 */
	protected $country = '';

	/**
	 * @var \SJBR\StaticInfoTables\Domain\Model\CountryZone
	 * @lazy
	 */
	protected $state = '';

	/**
	 * Initialize categories, attributed and media relation
	 */
	public function __construct() {
		$this->attributes =
			$this->categories =
			$this->content =
			$this->related =
			// $this->image =
			// $this->media =
				new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
	}

	/**
	 * @param string $additionaladdress
	 */
	public function setAdditionaladdress($additionaladdress) {
		$this->additionaladdress = $additionaladdress;
	}

	/**
	 * @return string
	 */
	public function getAdditionaladdress() {
		return $this->additionaladdress;
	}

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
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $attributes
	 */
	public function setAttributes($attributes) {
		$this->attributes = $attributes;
	}

	/**
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
	 */
	public function getAttributes() {
		return $this->attributes;
	}

	/**
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $categories
	 */
	public function setCategories($categories) {
		$this->categories = $categories;
	}

	/**
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
	 */
	public function getCategories() {
		return $this->categories;
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
	 * @param string $person
	 */
	public function setPerson($person) {
		$this->person = $person;
	}

	/**
	 * @return string
	 */
	public function getPerson() {
		return $this->person;
	}

	/**
	 * @param string $content
	 */
	public function setContent($content) {
		$this->content = $content;
	}

	/**
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * @param \SJBR\StaticInfoTables\Domain\Model\Country $country
	 */
	public function setCountry($country) {
		$this->country = $country;
	}

	/**
	 * @return \SJBR\StaticInfoTables\Domain\Model\Country
	 */
	public function getCountry() {
		return $this->country;
	}

	/**
	 * @return string
	 */
	public function getCountryName() {
		return $this->getCountry() ? $this->getCountry()->getShortNameEn() : '';
	}

	/**
	 * @param string $email
	 */
	public function setEmail($email) {
		$this->email = $email;
	}

	/**
	 * @return string
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * @param string $fax
	 */
	public function setFax($fax) {
		$this->fax = $fax;
	}

	/**
	 * @return string
	 */
	public function getFax() {
		return $this->fax;
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

	/**
	 * @param string $hours
	 */
	public function setHours($hours) {
		$this->hours = $hours;
	}

	/**
	 * @return string
	 */
	public function getHours() {
		return $this->hours;
	}

	/**
	 * @param string $icon
	 */
	public function setIcon($icon) {
		$this->icon = $icon;
	}

	/**
	 * @return string
	 */
	public function getIcon() {
		return $this->icon;
	}

	/**
	 * @param string $image
	 */
	public function setImage($image) {
		$this->image = $image;
	}

	/**
	 * @return string
	 */
	public function getImage() {
		return $this->image;
	}

	/**
	 * @param double $latitude
	 */
	public function setLatitude($latitude) {
		$this->latitude = (float) $latitude;
	}

	/**
	 * @return double
	 */
	public function getLatitude() {
		return (float) $this->latitude;
	}

	/**
	 * @param double $longitude
	 */
	public function setLongitude($longitude) {
		$this->longitude = (float) $longitude;
	}

	/**
	 * @return double
	 */
	public function getLongitude() {
		return (float) $this->longitude;
	}

	/**
	 * @param string $media
	 */
	public function setMedia($media) {
		$this->media = $media;
	}

	/**
	 * @return string
	 */
	public function getMedia() {
		return $this->media;
	}

	/**
	 * @param string $mobile
	 */
	public function setMobile($mobile) {
		$this->mobile = $mobile;
	}

	/**
	 * @return string
	 */
	public function getMobile() {
		return $this->mobile;
	}

	/**
	 * @param string $notes
	 */
	public function setNotes($notes) {
		$this->notes = $notes;
	}

	/**
	 * @return string
	 */
	public function getNotes() {
		return $this->notes;
	}

	/**
	 * @param string $phone
	 */
	public function setPhone($phone) {
		$this->phone = $phone;
	}

	/**
	 * @return string
	 */
	public function getPhone() {
		return $this->phone;
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
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $related
	 */
	public function setRelated($related) {
		$this->related = $related;
	}

	/**
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
	 */
	public function getRelated() {
		return $this->related;
	}

	/**
	 * @param \SJBR\StaticInfoTables\Domain\Model\CountryZone $state
	 */
	public function setState($state) {
		$this->state = $state;
	}

	/**
	 * @return \SJBR\StaticInfoTables\Domain\Model\CountryZone
	 */
	public function getState() {
		return $this->state;
	}

	/**
	 * @return \SJBR\StaticInfoTables\Domain\Model\CountryZone
	 */
	public function getStateName() {
		return $this->getState() ? $this->getState()->getNameEn() : '';
	}

	/**
	 * @param string $storeid
	 */
	public function setStoreid($storeid) {
		$this->storeid = $storeid;
	}

	/**
	 * @return string
	 */
	public function getStoreid() {
		return $this->storeid;
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
	 * @param string $url
	 */
	public function setUrl($url) {
		$this->url = $url;
	}

	/**
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
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
	 * @param int $useAsCenter
	 */
	public function setUseAsCenter($useAsCenter) {
		$this->useAsCenter = $useAsCenter;
	}

	/**
	 * @return int
	 */
	public function getUseAsCenter() {
		return $this->useAsCenter;
	}

	/**
	 * @param integer $zoom
	 */
	public function setZoom($zoom) {
		$this->zoom = $zoom;
	}

	/**
	 * @return integer
	 */
	public function getZoom() {
		return $this->zoom;
	}
}

?>