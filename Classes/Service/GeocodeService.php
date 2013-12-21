<?php
namespace Evoweb\StoreFinder\Service;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Sebastian Fischer <typo3@evoweb.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

class GeocodeService {
	/**
	 * @var array
	 */
	protected $settings = array();

	/**
	 * @param array $settings
	 */
	public function __construct(array $settings = array()) {
		if (count($settings)) {
			$this->setSettings($settings);
		}
	}

	/**
	 * @param array $settings
	 * @return void
	 */
	public function setSettings(array &$settings) {
		$this->settings = &$settings;

		$this->settings['geocodeLimit'] = $this->settings['geocodeLimit'] ? (int) $this->settings['geocodeLimit'] : '2500';
		$this->settings['geocodeUrl'] = $this->settings['geocodeUrl'] ?
			$this->settings['geocodeUrl'] :
			'http://maps.googleapis.com/maps/api/geocode/json?sensor=false';
	}

	/**
	 * @param \Evoweb\StoreFinder\Domain\Model\Location|\Evoweb\StoreFinder\Domain\Model\Constraint $location
	 * @return mixed
	 */
	public function geocodeAddress($location) {
			// Main Geocoder
		$query = $this->prepareQuery($location, array('address', 'zipcode', 'city', 'state_name', 'country_name'));
		$coordinate = $this->getCoordinateByQuery($query);

			// If there is no coordinat yet, we assume it's international and attempt to find it based on just the city and country.
		if (!$coordinate->lat && !$coordinate->lng) {
			$query = $this->prepareQuery($location, array('city', 'country'));
			$coordinate = $this->getCoordinateByQuery($query);
		}

		if ($coordinate->lat && $coordinate->lng) {
			$location->setLatitude($coordinate->lat);
			$location->setLongitude($coordinate->lng);
		}

		return $location;
	}

	/**
	 * @param \Evoweb\StoreFinder\Domain\Model\Location|\Evoweb\StoreFinder\Domain\Model\Constraint $location
	 * @param array $fields
	 * @return array
	 */
	protected function prepareQuery($location, $fields) {
			// for urlencoding
		$query = array();
		foreach ($fields as $field) {
			$methodName = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));
			$value = $location->{$methodName}();
			if (!is_object($value) && !is_array($value)) {
				$query[$field] = urlencode($value);
			}
		}

		return $query;
	}

	/**
	 * @param array $parameter
	 * @return \stdClass
	 */
	protected function getCoordinateByQuery($parameter) {
		$apiURL = $this->settings['geocodeUrl'] . '&address=' . implode(',+', $parameter);
		$addressData = json_decode(utf8_encode(\TYPO3\CMS\Core\Utility\GeneralUtility::getURL($apiURL)));

		if (property_exists($addressData, 'status') && $addressData->status === 'OK') {
			$result = $addressData->results[0]->geometry->location;
		} else {
			$result = new \stdClass();
		}

		return $result;
	}
}

?>