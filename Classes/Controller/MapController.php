<?php
namespace Evoweb\StoreFinder\Controller;
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

class MapController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {
	/**
	 * @var \Evoweb\StoreFinder\Domain\Repository\LocationRepository
	 */
	protected $repository;

	/**
	 * @return void
	 */
	protected function initializeAction() {
		$this->repository = $this->objectManager->get('Evoweb\StoreFinder\Domain\Repository\LocationRepository');
	}

	/**
	 * @param \Evoweb\StoreFinder\Domain\Model\Constraint $search
	 * @return void
	 */
	public function searchWithListMapAction(\Evoweb\StoreFinder\Domain\Model\Constraint $search = NULL) {
		if ($search !== NULL) {
			$locations = $this->repository->findAll();

			$this->view->assign('showResults', TRUE);
			$this->view->assign('center', $this->getCenter($search));
			$this->view->assign('numberOfLocations', $locations->count());
			$this->view->assign('locations', $locations);
		}
	}

	/**
	 * @return string
	 */
	protected function searchAction() {
	}

	/**
	 * @return void
	 */
	protected function mapAction() {
		$center = $this->getCenter();
		$locations = $this->repository->findAll();

		$this->view->assign('numberOfLocations', $locations->count());
		$this->view->assign('center', $center);
		$this->view->assign('locations', $locations);
	}

	/**
	 * @param \Evoweb\StoreFinder\Domain\Model\Constraint $constraint
	 * @return \Evoweb\StoreFinder\Domain\Model\Location
	 */
	protected function getCenter(\Evoweb\StoreFinder\Domain\Model\Constraint $constraint = NULL) {
		$center = NULL;

		if ($constraint !== NULL) {
			if ($constraint->getLatitude() && $constraint->getLongitude()) {
				/** @var \Evoweb\StoreFinder\Domain\Model\Location $center */
				$center = $this->objectManager->get('Evoweb\StoreFinder\Domain\Model\Location');
				$center->setLatitude($constraint->getLatitude());
				$center->setLongitude($constraint->getLongitude());
			} else {
				$center = $this->objectManager
					->get('Evoweb\StoreFinder\Service\GeocodeService', $this->settings)
					->geocodeAddress($constraint);
			}
		}

		if ($center === NULL) {
			$center = $this->repository->findOneByUseAsCenter(1);
		}

		if ($center === NULL) {
			$this->repository->setSettings($this->settings);
			$center = $this->repository->findCenter();
		}

		return $center;
	}

	private function _searchStore() {
		$this->cObj = t3lib_div :: makeInstance("tslib_cObj");
		$this->template = $this->cObj->fileResource($this->conf["templateFile"]);
		$start = array ();
		$this->_GP['start'] = array ();
		if ($this->_GP['lat']) {
			$center_lat_lon->lat = (float) $this->_GP['lat'];
			$center_lat_lon->lon = (float) $this->_GP['lon'];
		} else
			$center_lat_lon = $this->geocodeAddress($this->_GP);

		if (count($this->_GP['start']['city']) > 1) {
			// more than one city found for startingpoint
			// show city selection
			$subpart = $this->cObj->getSubpart($this->template, "###SEARCHCITY_HEADER###");
			$marksHeader['###MULTISTARTINGPOINTS###'] = $this->pi_getLL('multiStartingPoints');

			$final = $this->cObj->substituteMarkerArray($subpart, $marksHeader);

			$subpart = $this->cObj->getSubpart($this->template, "###SEARCHCITY_DATA###");
			$lon = (float) $this->_GP['start']['lon'];
			$lat = (float) $this->_GP['start']['lat'];
			$i = 0;

			while (list ($key, $v) = each($this->_GP['start']['city'])) {
				//$marks['###FORMHEADER###'] = $this->pi_getLL('formHeader');
				$marks['###FORMSTART###'] = '<form method="post" action="' . $this->pi_getPageLink($GLOBALS['TSFE']->id) . '">';
				$marks['###CITYV###'] = ($GLOBALS['TSFE']->localeCharset == 'utf-8') ? htmlspecialchars(utf8_encode($key)) : htmlspecialchars($key);
				$marks['###RADIUSV###'] = (int) $this->_GP['radius'];
				$marks['###COUNTRYV###'] = $this->_GP['country'];
				$marks['###LATV###'] = (float) $lat[$i];
				$marks['###LONV###'] = (float) $lon[$i];
				$marks['###STORESEARCH###'] = $this->pi_getLL('storeSearch');
				$i++;
				$final .= $this->cObj->substituteMarkerArray($subpart, $marks);

			}
			$final .= $this->cObj->getSubpart($this->template, "###SEARCHCITY_FOOTER###");
			return $final;
		}

		$HTTP_POST_VARS['lat_lon'] = $center_lat_lon;
		$this->_GP['lat_lon'] = $center_lat_lon;
		/*
		 * get the stores
		 */
		$mysqlResults = $this->_inradius($this->_GP, (int) $this->_GP['radius']);
		if ($mysqlResults == NULL) {
			return '<div class="searchResultHeader">' . $this->pi_getLL('noStoresFound') . '</div>';
		}
		$data['mysql'] = $mysqlResults;
		$data['center'] = $center_lat_lon;

		return $data;
	}

	private function _inradius($complete_address, $radius) {
		global $TYPO3_CONF_VARS;
		$_EXTCONF = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['locator']);

		if ($radius == 0)
			$radius = 1; // needed if 2 stores are nearly on same location
		if ($this->conf['distanceUnit'] == 'miles')
			$radius = $radius * 1.6;

		//If there are any limitations on the results set, get them here.
		$results_limit = $this->conf['resultLimit'];

		if ($results_limit > 0) {
			$results_limit = "0,$results_limit";
		}

		//debug($complete_address,'complete');
		//Check to see if we already have starting lat/lon so we dont do double geocoding
		// attention: $complete_address['lat_lon'] is of type stdClass
		if (!$complete_address['lat_lon']->lat && !$complete_address['lat_lon']->lon) {
			$lat_lon = $this->geocodeAddress($complete_address);
		} else {
			$lat_lon = $complete_address['lat_lon'];
		}
		$lat = (float) trim($lat_lon->lat);
		$lon = (float) trim($lat_lon->lon);

		if ($lat == '')
			return; // no coordinates found

		// when categories are set by Typoscript use them
		if ($this->conf['categories']) {
			$complete_address['categories'] = $this->conf['categories'];
		}

//		if ($this->_GP['category'])
//		    $categories = $this->_GP['category'];


		//Add in categories to the search criteria
		//If they are not an array, make them an array.
		$categories = $complete_address['categories'];

		if (!is_array($categories)) {
			$categories = explode(',', $categories);
		}
		// when calendar base is used change fieldname
		if ($this->conf['useFeUserData'] == 2)
			$categoryField = 'tx_locator_categoryuid';
		else
			if ($this->conf['useFeUserData'] == 3)
				$categoryField = 'category';
			else
				$categoryField = 'categoryuid';
		// here we have to fetch all subcategories if category is a parent
		// and we use locator_categories
		if ($this->conf['useFeUserData'] == 0 || $this->conf['useFeUserData'] == 4 || $this->conf['useFeUserData'] == 3) {
			foreach ($categories as $cat) {
				$cat = (int) trim($cat);
				if ($cat >= 0)
					$addCat = $this->getChildCategories($cat);
			}

			for ($i = 0; $i < count($addCat); $i++)
				$categories[] = $addCat[$i];
			if ($this->conf['useFeUserData'] == 0) {
				// get the correct categoryUid for the default language
				for ($i = 0; $i < count($categories); $i++) {
					$categories[$i] = $this->getL10nParentCategory($categories[$i]);
				}
			}

			$products = explode(',', $this->_GP['products']);
			if (count($products)) {
				$products_search = ' AND ( (';
				for ($i = 0; $i < count($products); $i++) {
					$products_search .= ($i != 0) ? ' OR ' : '';
					$products_search .= 'products like "%' . trim(strtoupper($products[$i])) . '%"';
				}
				// search in categorynames too
				$products_search .= ' ) OR (';
				for ($i = 0; $i < count($products); $i++) {
					$products_search .= ($i != 0) ? ' OR ' : '';

					$products_search .= 'b.name like "%' . trim(strtoupper($products[$i])) . '%"';
				}
				$products_search .= '))';
			}
		}

		reset($categories);
		if ($categories[0] == '')
			unset ($categories[0]);
		//Loop through and add each category to the query.


		$category_search = "AND (";
		if (count($categories) == 1) {
			if (!$categories[0]) {
				array_shift($categories);
			}

			$cat = (int) $categories[0]; // [0] -> [1] 13.01.2012    [1] -> [0] 29.02.2012
			$category_search .= " " . $categoryField . " = '$cat' ";
			$category_search .= " OR " . $categoryField . " LIKE '$cat,%' ";
			$category_search .= " OR " . $categoryField . " LIKE '%,$cat,%' ";
			$category_search .= " OR " . $categoryField . " LIKE '%,$cat' ";
		} else {
			foreach ($categories as $cat) {
				$cat = (int) trim($cat);
				if ($counter == 0) {
					$category_search .= " " . $categoryField . " = '$cat' ";
					$category_search .= " OR " . $categoryField . " = '' ";
					$category_search .= " OR " . $categoryField . " LIKE '$cat,%' ";
					$category_search .= " OR " . $categoryField . " LIKE '%,$cat,%' ";
					$category_search .= " OR " . $categoryField . " LIKE '%,$cat' ";
				} else {
					if ($cat != 0) {
						//                    $category_search .= " OR categories LIKE '%$cat%' ";
						$category_search .= " OR " . $categoryField . " = '$cat' ";
						$category_search .= " OR " . $categoryField . " LIKE '$cat,%' ";
						$category_search .= " OR " . $categoryField . " LIKE '%,$cat,%' ";
						$category_search .= " OR " . $categoryField . " LIKE '%,$cat' ";
					}
				}
				$counter++;
			}
		}
		$category_search .= ')';
		//debug($category_search);
		//debug($categories);

		// for tt_address special categorysearch
		if ($this->conf['useFeUserData'] == 4) {
			if ($categories[0] == '')
				array_shift($categories);
			for ($k = 0; $k < count($categories); $k++) {
				if ($k == 0)
					$cats = (int) $categories[$k];
				else
					$cats .= ',' . (int) $categories[$k];
			}
			$category_search = ' AND b.uid_local = a.uid AND b.uid_foreign in (' . $cats . ')';
		}

//		debug($category_search);
		//if (sizeof($categories) == 1 && $categories[0] == '') {
		// N. Badri 16.09.2011
		if (sizeof($categories) == 0 && $categories[0] == '') {
			$category_search = '';
		}

		$pi = M_PI;
		// calculating distance in km
		if ($this->conf['useFeUserData'] == 1) {
			$table = 'fe_users';
			if (t3lib_extMgm :: isLoaded('sr_feuser_register', 0))
				$fields = "*, uid as storeuid, www as url, name as storename, comments as notes, telephone as phone, zip as zipcode, tx_locator_lat as lat, tx_locator_lon as lon,(((acos(sin(($lat*$pi/180)) * sin((tx_locator_lat*$pi/180)) + cos(($lat*$pi/180)) *  cos((tx_locator_lat*$pi/180)) * cos((($lon - tx_locator_lon)*$pi/180)))))*6370) as distance";
			else // no comments field
				$fields = "*, uid as storeuid, www as url, name as storename,  telephone as phone, zip as zipcode, tx_locator_lat as lat, tx_locator_lon as lon,(((acos(sin(($lat*$pi/180)) * sin((tx_locator_lat*$pi/180)) + cos(($lat*$pi/180)) *  cos((tx_locator_lat*$pi/180)) * cos((($lon - tx_locator_lon)*$pi/180)))))*6370) as distance";

			if ($this->conf['displayMode'] == 'countryCitySelector') // search the city too
				$where_clause = 'city=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($this->_GP['city'], 'fe_users') . ' AND ';

			$where_clause .= " pid IN (" . $this->conf['pid_list'] . ") AND (" . $this->conf['feUserGroup'] . " in (usergroup) OR " .
			" usergroup like '%," . $this->conf['feUserGroup'] . "' OR " .
			" usergroup like '%," . $this->conf['feUserGroup'] . ",%')" .
			" AND deleted='0' AND disable='0' $category_search HAVING distance <= $radius";
		}
		if ($this->conf['useFeUserData'] == 0) {
			$table = 'tx_locator_locations a, tx_locator_categories b';
			$fields = " distinct a.*, a.uid as storeuid, imageurl as image, (((acos(sin(($lat*$pi/180)) * sin((lat*$pi/180)) + cos(($lat*$pi/180)) *  cos((lat*$pi/180)) * cos((($lon - lon)*$pi/180)))))*6370) as distance";
			$fields .= ", (select group_concat(c.name separator ', ') from tx_locator_categories c where (a.categoryuid = c.uid OR a.categoryuid like concat('%,',c.uid) OR a.categoryuid like concat(c.uid,',%') OR a.categoryuid like concat('%,', concat(c.uid,',%')))) as name";
			$fields .= ", (select group_concat(c.name, ':', c.icon separator ', ') from tx_locator_attributes c where (a.attributes = c.uid OR a.attributes like concat('%,',c.uid) OR a.attributes like concat(c.uid,',%') OR a.attributes like concat('%,', concat(c.uid,',%')))) as attributes";
			$where_clause = " a.pid IN (" . $this->conf['pid_list'] . ")";

			$where_clause .= str_replace('tx_locator_locations', 'a', $this->cObj->enableFields('tx_locator_locations'));

			if ($this->conf['limitResultsToCountry']) {
				$where_clause .= ' AND a.country = (select cn_short_en from static_countries where cn_iso_2 = "' . strtoupper($this->_GP['country']) . '")';
			}

/*
debug($this->conf['categories']);
			if ($this->conf['categories']) {
			    $category_search = ' AND a.categoryuid in (' . $this->conf['categories'] . ')';;
			}
*/
			if (count($products) > 0)
				$where_clause .= $products_search;
			if ($category_search != '')
				$where_clause .= " AND b.uid = a.categoryuid"; // this works even if there are multiple categories in categoryuid

			if ($this->conf['searchByName'])
				$where_clause .= ' AND storename like ' . $GLOBALS['TYPO3_DB']->fullQuoteStr('%' . $this->_GP['storename'] . '%', 'tx_locator_locations');

			$where_clause .= " AND a.deleted='0' AND a.hidden='0' $category_search HAVING distance <= $radius";

			if ($this->conf['singleView.']['showRelatedStores']) {
				$where_clause .= ' AND relatedto=""';
			}


			if ($this->conf['notepadId'] == $GLOBALS['TSFE']->id && $this->conf['enableNotepad'] && $_COOKIE['notepadStoreUids']) {
				if (preg_match('/^[0-9,]*$/', $_COOKIE['notepadStoreUids']) == 1)
				    $uidList = $_COOKIE['notepadStoreUids'];
				else $uidList = '';
			    $where_clause .= ' AND a.uid in (' . $uidList . ')';
			}
		}


		if ($this->conf['useFeUserData'] == 2) {
			$table = $this->conf['externalLocationTable'];
			$fields = "*, tx_locator_categoryuid as categoryuid, link as url, name as storename, description as notes,  zip as zipcode, tx_locator_lat as lat, tx_locator_lon as lon,(((acos(sin(($lat*$pi/180)) * sin((tx_locator_lat*$pi/180)) + cos(($lat*$pi/180)) *  cos((tx_locator_lat*$pi/180)) * cos((($lon - tx_locator_lon)*$pi/180)))))*6370) as distance";
			$where_clause = " pid IN (" . $this->conf['pid_list'] . ") AND deleted='0' AND hidden='0' $category_search HAVING distance <= $radius";
		}


		if ($this->conf['useFeUserData'] == 3) {
			$category_search = ' AND uid in (select uid_local from tt_news_cat_mm where uid_foreign in (' . implode(',', $categories) . '))';
			if ($this->conf['categories'])
				$category_search = ' AND uid in (select uid_local from tt_news_cat_mm where uid_foreign in (' . implode(',', $categories) . '))';

			$_categories = t3lib_div::trimExplode(',', $this->conf['categories'], 1);
			$_cat1 = array();
			$this->children = array();
			for ($i = 0; $i < count($_categories); $i++) {
				$this->children[] .= $_categories[$i];
				if ($_categories[$i] > '') {
				$_cat1 = array_merge($_cat1, $this->getChildCategories($_categories[$i]));
				$_cat1 = array_unique($_cat1);
				}
			}

            $this->conf['categories'] = $_cat1;
			if ($this->conf['categories'])
				$category_search = ' AND uid in (select uid_local from tt_news_cat_mm where uid_foreign in (' . implode(',', $_cat1) . '))';

			$table = 'tt_news a';
			$fields = "*, a.uid as storeuid, a.category as categoryuid, a.tx_locator_url as url, a.tx_locator_storename as storename, a.tx_locator_notes as notes," .
			" a.tx_locator_city as city, a.tx_locator_address as address, a.tx_locator_zipcode as zipcode," .
			" a.tx_locator_icon as icon, a.tx_locator_hours as hours, a.tx_locator_imageurl as image," .
			" a.tx_locator_phone as phone,  a.tx_locator_email as email, a.tx_locator_lat as lat, a.tx_locator_lon as lon,(((acos(sin(($lat*$pi/180)) * sin((tx_locator_lat*$pi/180)) + cos(($lat*$pi/180)) *  cos((tx_locator_lat*$pi/180)) * cos((($lon - tx_locator_lon)*$pi/180)))))*6370) as distance";
			$where_clause = " pid IN (" . $this->conf['pid_list'] . ") AND deleted='0' AND hidden='0' $category_search HAVING distance <= $radius";
			$where_clause .= " AND tx_locator_lat != 0 AND tx_locator_lon != 0";
			$where_clause .= " AND upper(title) like '%" . strtoupper($this->_GP['storename']) . "%'";
			$where_clause .= $whereCat;
		}

		//martin start
		if ($this->conf['useFeUserData'] == 4) {
			if (is_array($categories)) {
				for ($i = 0; $i < count($categories); $i++) {
					if ($i == 0)
						$cats = '(' . $categories[$i];
					else
						$cats .= ',' . $categories[$i];
				}
				$cats .= ')';
			}

			$table = 'tt_address a, tt_address_group_mm b';
			$fields = "distinct a.*, a.addressgroup as categoryuid, a.www as url, a.company as storename, a.description as notes," .
			" a.city, a.address, a.zip as zipcode," .
			" a.image,";


			if (!$this->conf['useApproximation'])
				$fields .= " a.phone,  a.email, a.tx_locator_lat as lat, a.tx_locator_lon as lon,(((acos(sin(($lat*$pi/180)) * sin((tx_locator_lat*$pi/180)) + cos(($lat*$pi/180)) *  cos((tx_locator_lat*$pi/180)) * cos((($lon - tx_locator_lon)*$pi/180)))))*6370) as distance";
			else {
				// approximation
				// dx = 71.5 * (lon1 - lon2); 71.5km per degree
				// dy = 111.3 * (lat1 - lat2)
				$fields .= " a.phone,  a.email, a.tx_locator_lat as lat, a.tx_locator_lon as lon, SQRT(POW($lon-tx_locator_lon,2) * 5112 + POW($lat-tx_locator_lat,2) * 12387) as distance";
			}
			//    if ($this->_GP['categories'] > '') {
			if ($this->conf['useTtaddressWithoutGroups']) {
				// may be it is a better way to create a temporary table
				// with a select with $lat < $latmax and $lat > $latmin etc
				// create temporary table ttaddresstemp as (select ...)
				// and then use the temporary table for distance
				$latmax = $lat + $radius / 111.3;
				$latmin = $lat - $radius / 111.3;
				$lonmax = $lon + $radius / 71.5;
				$lonmin = $lon - $radius / 71.5;
                $GLOBALS['TYPO3_DB']->admin_query('DROP TABLE IF EXISTS ttaddresstemp');
				$query = "CREATE TEMPORARY TABLE ttaddresstemp AS (SELECT * from tt_address where (tx_locator_lat < $latmax and tx_locator_lat > $latmin and
					tx_locator_lon < $lonmax AND tx_locator_lon > $lonmin))";

                $GLOBALS['TYPO3_DB']->admin_query($query);

				$table = 'tt_address a';
				$table = 'ttaddresstemp a';
                $category_search = '';
			}
// here seems a bug with $category_search
//$array = array ( 1 => $category_search);
//t3lib_div::devLog('addressData', 'tx_locator', 3, $array);
//$category_search = '';


			if (!$this->conf['useTtaddressWithoutGroups']) {
			$fields .= ",(select group_concat(d.title) from tt_address_group d where (
			                    d.uid =  (select group_concat(c.uid_foreign) from tt_address_group_mm c where c.uid_local = a.uid)
			                    or (select group_concat(c.uid_foreign) from tt_address_group_mm c where c.uid_local = a.uid) like concat('%,',d.uid)
			                    or (select group_concat(c.uid_foreign) from tt_address_group_mm c where c.uid_local = a.uid) like concat('%,',d.uid,',%')
			                    or (select group_concat(c.uid_foreign) from tt_address_group_mm c where c.uid_local = a.uid) like concat(d.uid,',%')
			                    )) as category";
			}
			//    }

			$where_clause .= " a.pid IN (" . $this->conf['pid_list'] . ") AND a.deleted='0' AND a.hidden='0' $category_search HAVING distance <= $radius";
//			$where_clause .= " a.pid IN (" . $this->conf['pid_list'] . ") AND a.deleted='0' AND a.hidden='0' $category_search";

//			$where_clause .= " AND (((acos(sin(($lat*$pi/180)) * sin((tx_locator_lat*$pi/180)) + cos(($lat*$pi/180)) *  cos((tx_locator_lat*$pi/180)) * cos((($lon - tx_locator_lon)*$pi/180)))))*6370) <=  $radius";

			$where_clause .= " AND tx_locator_lat != 0 AND tx_locator_lon != 0";
		}
		//martin stop

		if ($this->_GP['mode'] == 'mapAllNoFormView' || $this->conf['displayMode'] == 'mapAllView') {
			if ($this->conf['showAreas'])
				$orderBy = "lat desc";
			else
				$orderBy = "distance ASC";
			//$orderBy = "storename ASC";
			if ($this->conf['mapAllNoFormView.']['rand']) {
			    $orderBy = ' rand(now())';
				$results_limit = $this->conf['mapAllNoFormView.']['rand'];
			}

		} else
			$orderBy = "distance ASC";

		if ($this->conf['useFeUserData'] == 1) {
			$orderBy = 'tx_locator_toplocation desc, ' . $orderBy;
		}
		if ($this->conf['orderBy'])
		    $orderBy = $this->conf['orderBy'];


		$groupBy = "";
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $table, $where_clause, $groupBy, $orderBy, $results_limit);
		$i = 0;
		return $result;
	} // end func
}

?>