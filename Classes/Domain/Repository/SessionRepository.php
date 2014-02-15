<?php
namespace Evoweb\StoreFinder\Domain\Repository;
/***************************************************************
 * Copyright notice
 *
 * (c) 2014 Sebastian Fischer <typo3@evoweb.de>
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

use Evoweb\StoreFinder\Domain\Model;

/**
 * Class SessionRepository
 *
 * @package Evoweb\StoreFinder\Domain\Repository
 */
class SessionRepository {
	/**
	 * @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication
	 */
	protected $frontendUser;

	/**
	 * Constructor
	 *
	 * @return self
	 */
	public function __construct() {
		$this->frontendUser = $GLOBALS['TSFE']->fe_user;
	}

	/**
	 * Get coordinate by hash
	 *
	 * @param string $hash
	 * @return Model\Constraint|Model\Location|bool
	 */
	public function getCoordinateByHash($hash) {
		$sessionData = $this->frontendUser->getKey('ses', 'tx_storefinder_coordinates');

		$coordinate = isset($sessionData[$hash]) ? unserialize($sessionData[$hash]) : FALSE;

		return $coordinate;
	}

	/**
	 * Add calculated coordinate for hash
	 *
	 * @param Model\Constraint|Model\Location $coordinate
	 * @param string $hash
	 * @return void
	 */
	public function addCoordinateForHash($coordinate, $hash) {
		$sessionData = $this->frontendUser->getKey('ses', 'tx_storefinder_coordinates');
		$sessionData[$hash] = serialize($coordinate);

		$this->frontendUser->setKey('ses', 'tx_storefinder_coordinates', $sessionData);
		$this->frontendUser->storeSessionData();
	}
}