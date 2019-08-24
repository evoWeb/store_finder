<?php
declare(strict_types = 1);
namespace Evoweb\StoreFinder\Domain\Repository;

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

use Evoweb\StoreFinder\Domain\Model\Constraint;
use Evoweb\StoreFinder\Domain\Model\Location;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\EndTimeRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\StartTimeRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

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
     * @var int
     */
    const GLOBE_WIDTH = 256;

    /**
     * @var int
     */
    const ZOOM_MAX = 21;

    /**
     * @var array
     */
    protected $settings = [];

    public function setSettings(array $settings)
    {
        $this->settings = $settings;
    }

    public function findByUidInBackend(int $uid): Location
    {
        /** @var \TYPO3\CMS\Extbase\Persistence\Generic\Query $query */
        $query = $this->createQuery();

        $queryBuilder = $this->getQueryBuilderForTable('tx_storefinder_domain_model_location');
        $queryBuilder
            ->getRestrictions()
                ->removeByType(HiddenRestriction::class)
                ->removeByType(StartTimeRestriction::class)
                ->removeByType(EndTimeRestriction::class);

        $queryBuilder
            ->select('*')
            ->from('tx_storefinder_domain_model_location')
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid)));

        $sql = $queryBuilder->getSQL();

        $parameters = $queryBuilder->getParameters();
        $parameterType = $queryBuilder->getParameterTypes();
        array_walk($parameters, function ($value, $key) use (&$sql, $parameterType) {
            if ($parameterType[$key] == 2) {
                $sql = str_replace(':' . $key, '\'' . $value . '\'', $sql);
            } elseif ($parameterType[$key] == 101) {
                $sql = str_replace(':' . $key, implode(',', $value), $sql);
            } else {
                $sql = str_replace(':' .  $key, $value, $sql);
            }
        });

        $query->statement($sql);

        /** @var Location $location */
        $location = $query->execute()->getFirst();
        return $location;
    }

    public function findOneByUid(int $uid): QueryResultInterface
    {
        /** @var Query $query */
        $query = $this->createQuery();

        $query->matching($query->equals('uid', $uid));

        return $query->execute();
    }

    public function findByConstraint(Constraint $constraint): QueryResultInterface
    {
        /** @var \TYPO3\CMS\Extbase\Persistence\Generic\Query $query */
        $query = $this->createQuery();

        $queryBuilder = $this->getQueryBuilderForTable('tx_storefinder_domain_model_location');
        $queryBuilder
            ->from('tx_storefinder_domain_model_location', 'l');

        if (!$constraint->isGeocoded()) {
            $queryBuilder
                ->select('*')
                ->where(
                    // this comparison leads to an empty result, which
                    // is what we want if the constraint is not encoded.
                    $queryBuilder->expr()->eq('uid', PHP_INT_MAX)
                );
        } else {
            $queryBuilder
                ->selectLiteral(
                    'distinct l.*',
                    '(acos(
                        sin(' . $constraint->getLatitude() * M_PI . ' / 180) *
                        sin(latitude * ' . M_PI . ' / 180) +
                        cos(' . $constraint->getLatitude() * M_PI . ' / 180) *
                        cos(latitude * ' . M_PI . ' / 180) *
                        cos((' . $constraint->getLongitude() . ' - longitude) * ' . M_PI . ' / 180)
                    ) * 6370) as `distance`'
                )
                ->where(
                    $queryBuilder->expr()->in(
                        'l.pid',
                        $queryBuilder->createNamedParameter(
                            $query->getQuerySettings()->getStoragePageIds(),
                            \Doctrine\DBAL\Connection::PARAM_INT_ARRAY
                        )
                    )
                )
                ->orderBy('distance');

            $queryBuilder = $this->addCountryQueryPart($constraint, $queryBuilder);
            $queryBuilder = $this->addCategoryQueryPart($constraint, $queryBuilder);
            $queryBuilder = $this->addRadiusQueryPart($constraint, $queryBuilder);
            $queryBuilder = $this->addLimitQueryParts($constraint, $queryBuilder);
            $queryBuilder = $this->addFulltextSearchQueryParts($constraint, $queryBuilder);
        }

        $sql = $queryBuilder->getSQL();

        $parameters = $queryBuilder->getParameters();
        $parameterType = $queryBuilder->getParameterTypes();
        array_walk($parameters, function ($value, $key) use (&$sql, $parameterType) {
            if ($parameterType[$key] == 2) {
                $sql = str_replace(':' . $key, '\'' . $value . '\'', $sql);
            } elseif ($parameterType[$key] == 101) {
                $sql = str_replace(':' . $key, implode(',', $value), $sql);
            } else {
                $sql = str_replace(':' .  $key, $value, $sql);
            }
        });

        $query->statement($sql);

        return $query->execute();
    }

    protected function addCountryQueryPart(Constraint $constraint, QueryBuilder $queryBuilder): QueryBuilder
    {
        if ($constraint->getCountry()) {
            if (is_numeric($constraint->getCountry())) {
                $on = '(l.country = sc.uid)';
                $field = 'uid';
                $type = \PDO::PARAM_INT;
            } else {
                $on = '(l.country = sc.cn_iso_3)';
                $field = 'cn_iso_3';
                $type = \PDO::PARAM_STR;
            }

            $queryBuilder->innerJoin('l', 'static_countries', 'sc', $on);
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq('sc.' . $field, $queryBuilder->createNamedParameter(
                    $constraint->getCountry(),
                    $type
                ))
            );
        }

        return $queryBuilder;
    }

    protected function addCategoryQueryPart(Constraint $constraint, QueryBuilder $queryBuilder): QueryBuilder
    {
        if ($this->settings['categoryPriority'] == 'limitResultsToCategories') {
            $constraint->setCategory(GeneralUtility::intExplode(',', $this->settings['categories'], 1));
        } elseif ($this->settings['categoryPriority'] == 'useSelectedCategoriesIfNoFilterSelected'
            && !count($constraint->getCategory())
        ) {
            $constraint->setCategory(GeneralUtility::intExplode(',', $this->settings['categories'], 1));
        }

        $categories = $this->fetchCategoriesRecursive($constraint->getCategory());

        if (!empty($categories)) {
            $expression = $queryBuilder->expr();
            $queryBuilder->innerJoin(
                'l',
                'sys_category_record_mm',
                'c',
                (string) $expression->andX(
                    $expression->eq('l.uid', 'c.uid_foreign'),
                    $expression->eq(
                        'c.tablenames',
                        $queryBuilder->createNamedParameter('tx_storefinder_domain_model_location')
                    ),
                    $expression->eq(
                        'c.fieldname',
                        $queryBuilder->createNamedParameter('categories')
                    )
                )
            );
            $queryBuilder->andWhere(
                $expression->in(
                    'c.uid_local',
                    $queryBuilder->createNamedParameter($categories, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY)
                )
            );
        }

        return $queryBuilder;
    }

    protected function addRadiusQueryPart(Constraint $constraint, QueryBuilder $queryBuilder): QueryBuilder
    {
        if ($this->settings['distanceUnit'] == 'miles') {
            $constraint->setRadius(intval(max($constraint->getRadius(), 1) * 1.6));
        }

        $queryBuilder->having(
            '`distance` <= ' . $queryBuilder->createNamedParameter($constraint->getRadius(), \PDO::PARAM_STR)
        );

        return $queryBuilder;
    }

    protected function addLimitQueryParts(Constraint $constraint, QueryBuilder $queryBuilder): QueryBuilder
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
            $queryBuilder->setMaxResults($limit);
            $queryBuilder->setFirstResult($page);
        }

        return $queryBuilder;
    }

    protected function addFulltextSearchQueryParts(Constraint $constraint, QueryBuilder $queryBuilder): QueryBuilder
    {
        if ($constraint->getSearch()
            && isset($this->settings['fulltextSearchFields'])
            && is_array($this->settings['fulltextSearchFields'])
        ) {
            $fullTextSearchConstraint = [];
            $searchWordWrap = $this->settings['fulltextSearchWordWrap'] ?? '|';

            foreach ($this->settings['fulltextSearchFields'] as $searchField) {
                $fullTextSearchConstraint[] = $queryBuilder->expr()->like(
                    $searchField,
                    $queryBuilder->createNamedParameter(str_replace('|', $constraint->getSearch(), $searchWordWrap))
                );
            }

            if (count($fullTextSearchConstraint)) {
                $queryBuilder->andWhere($queryBuilder->expr()->orX($fullTextSearchConstraint));
            }
        }

        return $queryBuilder;
    }

    protected function fetchCategoriesRecursive(array $subcategories, array $categories = []): array
    {
        /** @var CategoryRepository $categoryRepository */
        $categoryRepository = $this->objectManager->get(CategoryRepository::class);

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

    public function findCenterByLatitudeAndLongitude(): Location
    {
        /** @var \TYPO3\CMS\Extbase\Persistence\Generic\Query $query */
        $query = $this->createQuery();

        $query->setOrderings(['latitude' => QueryInterface::ORDER_ASCENDING]);
        /** @var Location $minLatitude south */
        $minLatitude = $query->execute()->getFirst();

        // only search for the other locations if first succed or else we have no
        // locations at all
        if ($minLatitude === null) {
            $maxLatitude = $minLongitude = $maxLongitude = null;
        } else {
            $query->setOrderings(['latitude' => QueryInterface::ORDER_DESCENDING]);
            /** @var Location $maxLatitude north */
            $maxLatitude = $query->execute()->getFirst();

            $query->setOrderings(['longitude' => QueryInterface::ORDER_ASCENDING]);
            /** @var Location $minLongitude west */
            $minLongitude = $query->execute()->getFirst();

            $query->setOrderings(['longitude' => QueryInterface::ORDER_DESCENDING]);
            /** @var Location $maxLongitude east */
            $maxLongitude = $query->execute()->getFirst();
        }

        /** @var Location $location */
        $location = $this->objectManager->get(Location::class);
        $latitudeZoom = $longitudeZoom = 0;

        /**
         * http://stackoverflow.com/questions/6048975
         *    /google-maps-v3-how-to-calculate-the-zoom-level-for-a-given-bounds
         */
        if ($minLatitude !== null && $maxLatitude !== null) {
            $location->setLatitude(($maxLatitude->getLatitude() + $minLatitude->getLatitude()) / 2);
            $latitudeFraction = (
                $this->latRad($maxLatitude->getLatitude())
                - $this->latRad($minLatitude->getLatitude())
                ) / M_PI;
            $latitudeZoom = $this->zoom($this->settings['mapSize']['height'], self::GLOBE_WIDTH, $latitudeFraction);
        }

        if ($minLongitude !== null && $maxLongitude !== null) {
            $location->setLongitude(($maxLongitude->getLongitude() + $minLongitude->getLongitude()) / 2);
            $longitudeDiff = $maxLongitude->getLongitude() - $minLongitude->getLongitude();
            $longitudeFraction = (($longitudeDiff < 0) ? ($longitudeDiff + 360) : $longitudeDiff) / 360;
            $longitudeZoom = $this->zoom($this->settings['mapSize']['width'], self::GLOBE_WIDTH, $longitudeFraction);
        }

        if ($latitudeZoom > 0 || $longitudeZoom > 0) {
            $location->setZoom(min($latitudeZoom, $longitudeZoom, self::ZOOM_MAX));
        }

        return $location;
    }

    public function findOneByCenter(): ?Location
    {
        /** @var \TYPO3\CMS\Extbase\Persistence\Generic\Query $query */
        $query = $this->createQuery();

        $query->setOrderings(['sorting' => QueryInterface::ORDER_ASCENDING]);
        $query->matching($query->equals('center', 1));

        /** @var Location $location */
        $location = $query->execute()->getFirst();
        return $location;
    }

    /**
     * Rad calculation of latitude value
     *
     * @param float $latitude
     *
     * @return float
     */
    protected function latRad(float $latitude): float
    {
        $sin = sin($latitude * M_PI / 180);
        $radX2 = log((1 + $sin) / (1 - $sin)) / 2;

        return max(min($radX2, M_PI), -M_PI) / 2;
    }

    /**
     * Calculate the map radius
     *
     * @param int $mapPx
     * @param int $worldPx
     * @param float $fraction
     *
     * @return float
     */
    protected function zoom(int $mapPx, int $worldPx, float $fraction): float
    {
        return floor(log($mapPx / $worldPx / $fraction) / self::MATH_LN2);
    }

    /**
     * Query location repository for all locations that
     * have latitude or longitude empty or geocode set to 1
     *
     * @param int $limit
     *
     * @return QueryResultInterface
     */
    public function findAllWithoutLatLon(int $limit = 500): QueryResultInterface
    {
        /** @var Query $query */
        $query = $this->createQuery();
        $query->setLimit($limit);
        $query->getQuerySettings()->setRespectStoragePage(false);

        $query->matching(
            $query->logicalOr(
                $query->equals('geocode', 1),
                $query->logicalOr(
                    $query->equals('latitude', 0),
                    $query->equals('longitude', 0)
                )
            )
        );

        return $query->execute();
    }

    protected function getQueryBuilderForTable(string $table): QueryBuilder
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Database\ConnectionPool::class
        )->getQueryBuilderForTable($table);
    }
}
