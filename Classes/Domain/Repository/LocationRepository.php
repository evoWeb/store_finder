<?php
namespace Evoweb\StoreFinder\Domain\Repository;
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

class LocationRepository extends \TYPO3\CMS\Extbase\Persistence\Repository  {
	/**
	 * Natural logarithm of 2
	 *
	 * @var float
	 */
	const MATH_LN2 = 0.69314718055995;

	/**
	 * A constant in Google's map projection
	 *
	 * @var integer
	 */
	const GLOBE_WIDTH = 256;

	/**
	 * @var integer
	 */
	const ZOOM_MAX = 21;

	/**
	 * @var array
	 */
	protected $settings = array();

	/**
	 * @param array $settings
	 * @return void
	 */
	public function setSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * @return \Evoweb\StoreFinder\Domain\Model\Location
	 */
	public function findCenter() {
		/** @var \TYPO3\CMS\Extbase\Persistence\Generic\Query $query */
		$query = $this->createQuery();

		$query->setOrderings(array('latitude' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING));
		/** @var \Evoweb\StoreFinder\Domain\Model\Location $minLatitude south */
		$minLatitude = $query->execute()->getFirst();

			// only search for the other locations if first succed or else we have no locations at all
		if ($minLatitude === NULL) {
			$maxLatitude = $minLongitute = $maxLongitute = NULL;
		} else {
			$query->setOrderings(array('latitude' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING));
			/** @var \Evoweb\StoreFinder\Domain\Model\Location $maxLatitude north */
			$maxLatitude = $query->execute()->getFirst();

			$query->setOrderings(array('longitude' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING));
			/** @var \Evoweb\StoreFinder\Domain\Model\Location $minLongitute west */
			$minLongitute = $query->execute()->getFirst();

			$query->setOrderings(array('longitude' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING));
			/** @var \Evoweb\StoreFinder\Domain\Model\Location $maxLongitute east */
			$maxLongitute = $query->execute()->getFirst();
		}

		/** @var \Evoweb\StoreFinder\Domain\Model\Location $location */
		$location = $this->objectManager->get('Evoweb\StoreFinder\Domain\Model\Location');
		$latitudeZoom = $longitudeZoom = 0;

		/**
		 * http://stackoverflow.com/questions/6048975/google-maps-v3-how-to-calculate-the-zoom-level-for-a-given-bounds
		 */
		if ($minLatitude !== NULL && $maxLatitude !== NULL) {
			$location->setLatitude(($maxLatitude->getLatitude() + $minLatitude->getLatitude()) / 2);
			$latitudeFraction = ($this->latRad($maxLatitude->getLatitude()) - $this->latRad($minLatitude->getLatitude())) / M_PI;
			$latitudeZoom = $this->zoom($this->settings['mapSize']['height'], self::GLOBE_WIDTH, $latitudeFraction);
		}

		if ($minLongitute !== NULL && $maxLongitute !== NULL) {
			$location->setLongitude(($maxLongitute->getLongitude() + $minLongitute->getLongitude()) / 2);
			$longitudeDiff = $maxLongitute->getLongitude() - $minLongitute->getLongitude();
			$longitudeFraction = (($longitudeDiff < 0) ? ($longitudeDiff + 360) : $longitudeDiff) / 360;
			$longitudeZoom = $this->zoom($this->settings['mapSize']['width'], self::GLOBE_WIDTH, $longitudeFraction);
		}

		if ($latitudeZoom > 0 || $longitudeZoom > 0) {
			$location->setZoom(min($latitudeZoom, $longitudeZoom, self::ZOOM_MAX));
		}

		return $location;
	}

	/**
	 * @param float $latitude
	 * @return string
	 */
	protected function latRad($latitude) {
		$sin = sin($latitude * M_PI / 180);
		$radX2 = log((1 + $sin) / (1 - $sin)) / 2;
		return max(min($radX2, M_PI), - M_PI) / 2;
	}

	/**
	 * @param integer $mapPx
	 * @param integer $worldPx
	 * @param float $fraction
	 * @return float
	 */
	protected function zoom($mapPx, $worldPx, $fraction) {
		return floor(log($mapPx / $worldPx / $fraction) / self::MATH_LN2);
	}
}

?>