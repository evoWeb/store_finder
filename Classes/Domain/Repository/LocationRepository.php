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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class LocationRepository
 *
 * @package Evoweb\StoreFinder\Domain\Repository
 */
class LocationRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
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
     * Setter
     *
     * @param array $settings
     *
     * @return void
     */
    public function setSettings(array $settings)
    {
        $this->settings = $settings;
    }


    /**
     * Find locations by contraint
     *
     * @param \Evoweb\StoreFinder\Domain\Model\Constraint $constraint
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findByConstraint($constraint)
    {
        /** @var \TYPO3\CMS\Extbase\Persistence\Generic\Query $query */
        $query = $this->createQuery();

        if (!$constraint->isGeocoded()) {
            $queryParts = array(
                'SELECT' => '*',
                'FROM' => 'tx_storefinder_domain_model_location l',
                'WHERE' => '1=2',
                'GROUPBY' => '',
                'ORDERBY' => '',
                'LIMIT' => '',
            );
        } else {
            $queryParts = array(
                'SELECT' => '
                distinct l.*,
                (acos(
                    sin(' . $constraint->getLatitude() * M_PI . ' / 180) *
                    sin(latitude * ' . M_PI . ' / 180) +
                    cos(' . $constraint->getLatitude() * M_PI . ' / 180) *
                    cos(latitude * ' . M_PI . ' / 180) *
                    cos((' . $constraint->getLongitude() . ' - longitude) * ' . M_PI . ' / 180)
                ) * 6370) as distance',
                'FROM' => 'tx_storefinder_domain_model_location l',
                'WHERE' => 'l.pid IN (' . implode(',', $query->getQuerySettings()->getStoragePageIds()) . ')' .
                $this->getWhereClauseForEnabledFields('tx_storefinder_domain_model_location', 'l'),
                'GROUPBY' => '',
                'ORDERBY' => 'distance',
                'LIMIT' => '',
            );

            $queryParts = $this->addCountryQueryPart($constraint, $queryParts);
            $queryParts = $this->addCategoryQueryPart($constraint, $queryParts);
            $queryParts = $this->addRadiusQueryPart($constraint, $queryParts);
            $queryParts = $this->addLimitQueryParts($constraint, $queryParts);
        }
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $database */
        $database = $GLOBALS['TYPO3_DB'];

        $sql = $database->SELECTquery(
            $queryParts['SELECT'],
            $queryParts['FROM'],
            $queryParts['WHERE'],
            $queryParts['GROUPBY'],
            $queryParts['ORDERBY'],
            $queryParts['LIMIT']
        );
        $query->statement($sql);

        return $query->execute();
    }

    /**
     * Adds country to query parts if present in contraints
     *
     * @param \Evoweb\StoreFinder\Domain\Model\Constraint $constraint
     * @param array $queryParts
     *
     * @return array
     */
    protected function addCountryQueryPart($constraint, $queryParts)
    {
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $database */
        $database = $GLOBALS['TYPO3_DB'];

        if ($constraint->getCountry()) {
            $country = $constraint->getCountry();
            if (is_numeric($country)) {
                $queryParts['FROM'] .= ' INNER JOIN static_countries sc ON (l.country = sc.uid)';
                $queryParts['WHERE'] .= ' AND sc.uid = ' . $country;
            } else {
                $queryParts['FROM'] .= ' INNER JOIN static_countries sc ON (l.country = sc.cn_iso_3)';
                $queryParts['WHERE'] .= ' AND sc.cn_iso_3 = '
                    . $database->fullQuoteStr(strtoupper($constraint->getCountry()), 'static_countries');
            }


        }

        return $queryParts;
    }

    /**
     * Adds categories to query parts if present in contraints
     *
     * @param \Evoweb\StoreFinder\Domain\Model\Constraint $constraint
     * @param array $queryParts
     *
     * @return array
     */
    protected function addCategoryQueryPart($constraint, $queryParts)
    {
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $database */
        $database = $GLOBALS['TYPO3_DB'];

        if ($this->settings['categoryPriority'] == 'limitResultsToCategories') {
            $constraint->setCategory(GeneralUtility::intExplode(',', $this->settings['categories'], 1));
        } elseif ($this->settings['categoryPriority'] == 'useSelectedCategoriesIfNoFilterSelected'
            && !count($constraint->getCategory())
        ) {
            $constraint->setCategory(GeneralUtility::intExplode(',', $this->settings['categories'], 1));
        }

        $categories = $this->fetchCategoriesRecursive($constraint->getCategory());

        if (!empty($categories)) {
            $queryParts['FROM'] .= ' INNER JOIN sys_category_record_mm c ON (l.uid = c.uid_foreign
				AND c.tablenames = \'tx_storefinder_domain_model_location\' AND c.fieldname = \'categories\')';
            $queryParts['WHERE'] .= ' AND c.uid_local IN (' . implode(',', $database->cleanIntArray($categories)) . ')';
        }

        return $queryParts;
    }

    /**
     * Adds radius to query parts if present in contraints
     *
     * @param \Evoweb\StoreFinder\Domain\Model\Constraint $constraint
     * @param array $queryParts
     *
     * @return array
     */
    protected function addRadiusQueryPart($constraint, $queryParts)
    {
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $database */
        $database = $GLOBALS['TYPO3_DB'];

        if ($this->settings['distanceUnit'] == 'miles') {
            $constraint->setRadius(max($constraint->getRadius(), 1) * 1.6);
        }
        $queryParts['WHERE'] .= ' HAVING distance <= ' . $database->fullQuoteStr($constraint->getRadius(), '');

        return $queryParts;
    }

    /**
     * Add limit to query parts
     *
     * @param \Evoweb\StoreFinder\Domain\Model\Constraint $constraint
     * @param array $queryParts
     *
     * @return array
     */
    protected function addLimitQueryParts($constraint, $queryParts)
    {
        $limit = (int) $this->settings['limit'];
        $page = 0;

        if ($constraint->getLimit()) {
            $limit = $constraint->getLimit();
        }

        if ($constraint->getPage()) {
            $page = $constraint->getPage() * $limit;
        }

        if ($limit) {
            $queryParts['LIMIT'] = $page . ',' . $limit;
        }

        return $queryParts;
    }

    /**
     * Fetch categories recursive
     *
     * @param array|int $subcategories
     * @param array $categories
     *
     * @return array
     */
    protected function fetchCategoriesRecursive(array $subcategories, $categories = array())
    {
        /** @var CategoryRepository $categoryRepository */
        $categoryRepository = $this->objectManager->get('Evoweb\\StoreFinder\\Domain\\Repository\\CategoryRepository');

        /** @var \Evoweb\StoreFinder\Domain\Model\Category $subcategory */
        foreach ($subcategories as $subcategory) {
            $categories[] = $subcategoryUid = (int) (is_object($subcategory) ? $subcategory->getUid() : $subcategory);

            /** @noinspection PhpUndefinedMethodInspection */
            /** @var \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult $foundCategories */
            $foundCategories = $categoryRepository->findByParent($subcategoryUid);
            $foundCategories->rewind();

            $categories = $this->fetchCategoriesRecursive($foundCategories->toArray(), $categories);
        }

        return array_unique($categories);
    }


    /**
     * Find center for latitude and longitude
     *
     * @return \Evoweb\StoreFinder\Domain\Model\Location
     */
    public function findCenterByLatitudeAndLongitude()
    {
        /** @var \TYPO3\CMS\Extbase\Persistence\Generic\Query $query */
        $query = $this->createQuery();

        $query->setOrderings(array('latitude' => QueryInterface::ORDER_ASCENDING));
        /** @var \Evoweb\StoreFinder\Domain\Model\Location $minLatitude south */
        $minLatitude = $query->execute()->getFirst();

        // only search for the other locations if first succed or else we have no
        // locations at all
        if ($minLatitude === null) {
            $maxLatitude = $minLongitute = $maxLongitute = null;
        } else {
            $query->setOrderings(array('latitude' => QueryInterface::ORDER_DESCENDING));
            /** @var \Evoweb\StoreFinder\Domain\Model\Location $maxLatitude north */
            $maxLatitude = $query->execute()->getFirst();

            $query->setOrderings(array('longitude' => QueryInterface::ORDER_ASCENDING));
            /** @var \Evoweb\StoreFinder\Domain\Model\Location $minLongitute west */
            $minLongitute = $query->execute()->getFirst();

            $query->setOrderings(array('longitude' => QueryInterface::ORDER_DESCENDING));
            /** @var \Evoweb\StoreFinder\Domain\Model\Location $maxLongitute east */
            $maxLongitute = $query->execute()->getFirst();
        }

        /** @var \Evoweb\StoreFinder\Domain\Model\Location $location */
        $location = $this->objectManager->get('Evoweb\StoreFinder\Domain\Model\Location');
        $latitudeZoom = $longitudeZoom = 0;

        /**
         * http://stackoverflow.com/questions/6048975
         *    /google-maps-v3-how-to-calculate-the-zoom-level-for-a-given-bounds
         */
        if ($minLatitude !== null && $maxLatitude !== null) {
            $location->setLatitude(($maxLatitude->getLatitude() + $minLatitude->getLatitude()) / 2);
            $latitudeFraction = ($this->latRad($maxLatitude->getLatitude())
                    - $this->latRad($minLatitude->getLatitude())) / M_PI;
            $latitudeZoom = $this->zoom($this->settings['mapSize']['height'], self::GLOBE_WIDTH, $latitudeFraction);
        }

        if ($minLongitute !== null && $maxLongitute !== null) {
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
     * Find one location that is flagged as center
     *
     * @return \Evoweb\StoreFinder\Domain\Model\Location
     */
    public function findOneByCenter()
    {
        /** @var \TYPO3\CMS\Extbase\Persistence\Generic\Query $query */
        $query = $this->createQuery();

        $query->setOrderings(array('sorting' => QueryInterface::ORDER_ASCENDING));
        $query->matching($query->equals('center', 1));

        return $query->execute()->getFirst();
    }

    /**
     * Rad method for latitude calculation
     *
     * @param float $latitude
     *
     * @return string
     */
    protected function latRad($latitude)
    {
        $sin = sin($latitude * M_PI / 180);
        $radX2 = log((1 + $sin) / (1 - $sin)) / 2;

        return max(min($radX2, M_PI), -M_PI) / 2;
    }

    /**
     * Calculate the map radius
     *
     * @param integer $mapPx
     * @param integer $worldPx
     * @param float $fraction
     *
     * @return float
     */
    protected function zoom($mapPx, $worldPx, $fraction)
    {
        return floor(log($mapPx / $worldPx / $fraction) / self::MATH_LN2);
    }

    /**
     * Get where clause for enable fields
     *
     * @param string $table
     * @param string $replacement
     *
     * @return string
     */
    protected function getWhereClauseForEnabledFields($table, $replacement = '')
    {
        if (TYPO3_MODE === 'FE') {
            // frontend context
            $whereClause = $GLOBALS['TSFE']->sys_page->enableFields($table);
            $whereClause .= $GLOBALS['TSFE']->sys_page->deleteClause($table);
        } else {
            // backend context
            $whereClause = BackendUtility::BEenableFields($table);
            $whereClause .= BackendUtility::deleteClause($table);
        }

        if ($replacement) {
            $whereClause = str_replace($table, $replacement, $whereClause);
        }

        return $whereClause;
    }


    /**
     * Query location repository for all locations that
     * have latitude or longitude empty or geocode set to 1
     *
     * @param int $limit
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findAllWithoutLatLon($limit = 500)
    {
        /** @var Query $query */
        $query = $this->createQuery();

        $query->matching(
            $query->logicalOr(
                $query->equals('geocode', 1),
                $query->logicalOr(
                    $query->equals('latitude', 0),
                    $query->equals('longitude', 0)
                )
            )
        );
        $query->setLimit($limit);
        $query->getQuerySettings()->setRespectStoragePage(false);

        return $query->execute();
    }
}
