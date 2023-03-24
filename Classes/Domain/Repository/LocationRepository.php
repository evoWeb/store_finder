<?php

declare(strict_types=1);

namespace Evoweb\StoreFinder\Domain\Repository;

/*
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Doctrine\DBAL\ArrayParameterType;
use Evoweb\StoreFinder\Domain\Model\Constraint;
use Evoweb\StoreFinder\Domain\Model\Location;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class LocationRepository extends Repository
{
    /**
     * @var array
     */
    protected $defaultOrderings = [
        'zipcode' => QueryInterface::ORDER_ASCENDING,
        'city' => QueryInterface::ORDER_ASCENDING,
        'name' => QueryInterface::ORDER_ASCENDING,
    ];

    /**
     * Natural logarithm of 2
     *
     * @var float
     */
    public const MATH_LN2 = 0.69314718055995;

    /**
     * A constant in Google's map projection
     *
     * @var int
     */
    public const GLOBE_WIDTH = 256;

    /**
     * @var int
     */
    public const ZOOM_MAX = 21;

    protected array $settings = [];

    public function __construct(
        protected ConnectionPool $connectionPool,
        protected CategoryRepository $categoryRepository
    ) {
        parent::__construct();
        $this->objectType = Location::class;
    }

    public function setSettings(array $settings): void
    {
        $this->settings = $settings;
    }

    public function findByUidInBackend(int $uid): ?Location
    {
        /** @var Query $query */
        $query = $this->createQuery();
        $query->getQuerySettings()
            ->setIgnoreEnableFields(true)
            ->setRespectStoragePage(false);

        /** @var Location $location */
        $location = $query
            ->matching($query->equals('uid', $uid))
            ->execute()
            ->getFirst();

        return $location;
    }

    public function findOneByUid(int $uid): QueryResultInterface
    {
        /** @var Query $query */
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        $query->matching($query->equals('uid', $uid));

        return $query->execute();
    }

    public function findByConstraint(Constraint $constraint): QueryResultInterface
    {
        /** @var Query $query */
        $query = $this->createQuery();

        $tableName = 'tx_storefinder_domain_model_location';
        $queryBuilder = $this->getQueryBuilderForTable($tableName);
        $queryBuilder
            ->from($tableName, 'l');

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
                            ArrayParameterType::INTEGER
                        )
                    )
                )
                ->orderBy('distance');

            $queryBuilder = $this->addCountryQueryPart($constraint, $queryBuilder);
            $queryBuilder = $this->addCategoryQueryPart($constraint, $queryBuilder);
            $queryBuilder = $this->addRadiusQueryPart($constraint, $queryBuilder);
            $queryBuilder = $this->addLimitQueryParts($constraint, $queryBuilder);
            $queryBuilder = $this->addFulltextSearchQueryParts($constraint, $queryBuilder);
            $queryBuilder = $this->addLanguagePart($tableName, 'l', $queryBuilder);
        }

        $sql = QueryBuilderHelper::getStatement($queryBuilder);
        $query->statement($sql);

        return $query->execute();
    }

    protected function addCountryQueryPart(Constraint $constraint, QueryBuilder $queryBuilder): QueryBuilder
    {
        $value = $constraint->getCountry();
        if ($value) {
            $queryBuilder->innerJoin('l', 'static_countries', 'sc', '(l.country = sc.uid)');
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq(
                    'sc.uid',
                    $queryBuilder->createNamedParameter($value->getUid(), \PDO::PARAM_INT)
                )
            );
        }

        return $queryBuilder;
    }

    protected function addCategoryQueryPart(Constraint $constraint, QueryBuilder $queryBuilder): QueryBuilder
    {
        if ($this->settings['categoryPriority'] == 'limitResultsToCategories') {
            $constraint->setCategory(GeneralUtility::intExplode(',', $this->settings['categories'], 1));
        } elseif (
            $this->settings['categoryPriority'] == 'useSelectedCategoriesIfNoFilterSelected'
            && !count($constraint->getCategory())
        ) {
            $constraint->setCategory(GeneralUtility::intExplode(',', $this->settings['categories'], 1));
        }

        $categories = $this->categoryRepository->findByParentRecursive($constraint->getCategory());

        if (!empty($categories)) {
            $expression = $queryBuilder->expr();
            $queryBuilder->innerJoin(
                'l',
                'sys_category_record_mm',
                'c',
                (string)$expression->and(
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
                    $queryBuilder->createNamedParameter($categories, ArrayParameterType::INTEGER)
                )
            );
        }

        return $queryBuilder;
    }

    protected function addRadiusQueryPart(Constraint $constraint, QueryBuilder $queryBuilder): QueryBuilder
    {
        if ($this->settings['distanceUnit'] == 'miles') {
            $constraint->setRadius((int)(max($constraint->getRadius(), 1) * 1.6));
        }

        $queryBuilder->having(
            '`distance` <= ' . $queryBuilder->createNamedParameter($constraint->getRadius())
        );

        return $queryBuilder;
    }

    protected function addLanguagePart(
        string $tableName,
        string $tableAlias,
        QueryBuilder $queryBuilder
    ): QueryBuilder {
        if (empty($GLOBALS['TCA'][$tableName]['ctrl']['languageField'])) {
            return $queryBuilder;
        }

        /** @var Context $context */
        $context = GeneralUtility::makeInstance(Context::class);
        /** @var LanguageAspect $languageAspect */
        $languageAspect = $context->getAspect('language');

        // Select all entries for the current language
        // If any language is set -> get those entries which are not translated yet
        $languageField = $GLOBALS['TCA'][$tableName]['ctrl']['languageField'];

        $transOrigPointerField = $GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField'] ?? '';
        if (!$transOrigPointerField || !$languageAspect->getContentId()) {
            $queryBuilder->andWhere($queryBuilder->expr()->in(
                $tableAlias . '.' . $languageField,
                [$languageAspect->getContentId(), -1]
            ));
            return $queryBuilder;
        }

        $mode = $languageAspect->getOverlayType();
        if (
            !in_array($mode, [
                LanguageAspect::OVERLAYS_ON_WITH_FLOATING,
                LanguageAspect::OVERLAYS_ON,
                LanguageAspect::OVERLAYS_MIXED
            ])
        ) {
            $queryBuilder->andWhere($queryBuilder->expr()->in(
                $tableAlias . '.' . $languageField,
                [$languageAspect->getContentId(), -1]
            ));
            return $queryBuilder;
        }

        $defLangTableAlias = $tableAlias . '_dl';
        $defaultLanguageRecordsSubSelect = $queryBuilder->getConnection()->createQueryBuilder();
        $defaultLanguageRecordsSubSelect
            ->select($defLangTableAlias . '.uid')
            ->from($tableName, $defLangTableAlias)
            ->where(
                $defaultLanguageRecordsSubSelect->expr()->and(
                    $defaultLanguageRecordsSubSelect->expr()->eq($defLangTableAlias . '.' . $transOrigPointerField, 0),
                    $defaultLanguageRecordsSubSelect->expr()->eq($defLangTableAlias . '.' . $languageField, 0)
                )
            );

        $andConditions = [];
        // records in language 'all'
        $andConditions[] = $queryBuilder->expr()->eq($tableAlias . '.' . $languageField, -1);
        // translated records where a default language exists
        $andConditions[] = $queryBuilder->expr()->and(
            $queryBuilder->expr()->eq($tableAlias . '.' . $languageField, $languageAspect->getContentId()),
            $queryBuilder->expr()->in(
                $tableAlias . '.' . $transOrigPointerField,
                $defaultLanguageRecordsSubSelect->getSQL()
            )
        );
        if ($mode === LanguageAspect::OVERLAYS_MIXED) {
            // $mode = TRUE
            // returns records from current language which have default language
            // together with not translated default language records
            $translatedOnlyTableAlias = $tableAlias . '_to';
            $queryBuilderForSubSelect = $queryBuilder->getConnection()->createQueryBuilder();
            $queryBuilderForSubSelect
                ->select($translatedOnlyTableAlias . '.' . $transOrigPointerField)
                ->from($tableName, $translatedOnlyTableAlias)
                ->where(
                    $queryBuilderForSubSelect->expr()->and(
                        $queryBuilderForSubSelect->expr()->gt(
                            $translatedOnlyTableAlias . '.' . $transOrigPointerField,
                            0
                        ),
                        $queryBuilderForSubSelect->expr()->eq(
                            $translatedOnlyTableAlias . '.' . $languageField,
                            $languageAspect->getContentId()
                        )
                    )
                );
            // records in default language, which do not have a translation
            $andConditions[] = $queryBuilder->expr()->and(
                $queryBuilder->expr()->eq($tableAlias . '.' . $languageField, 0),
                $queryBuilder->expr()->notIn(
                    $tableAlias . '.uid',
                    $queryBuilderForSubSelect->getSQL()
                )
            );
        }

        $queryBuilder->andWhere($queryBuilder->expr()->or(...$andConditions));
        return $queryBuilder;
    }

    protected function addLimitQueryParts(Constraint $constraint, QueryBuilder $queryBuilder): QueryBuilder
    {
        $limit = (int)$this->settings['limit'];
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
        if (
            $constraint->getSearch()
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
                $queryBuilder->andWhere($queryBuilder->expr()->or(...$fullTextSearchConstraint));
            }
        }

        return $queryBuilder;
    }

    public function findCenterByLatitudeAndLongitude(): Location
    {
        /** @var Query $query */
        $query = $this->createQuery();

        $query->setOrderings(['latitude' => QueryInterface::ORDER_ASCENDING]);
        /** @var Location $minLatitude south */
        $minLatitude = $query->execute()->getFirst();

        // only search for the other locations if first succeed or else we have no locations at all
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
        $location = GeneralUtility::makeInstance(Location::class);
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
        /** @var Query $query */
        $query = $this->createQuery();

        $query->setOrderings(['sorting' => QueryInterface::ORDER_ASCENDING]);
        $query->matching($query->equals('center', 1));

        /** @var Location $location */
        $location = $query->execute()->getFirst();
        return $location;
    }

    /**
     * Rad calculation of latitude value
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
        return $this->connectionPool->getQueryBuilderForTable($table);
    }

    public function getEmptyResult(): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching($query->lessThan('pid', 0));
        return $query->execute();
    }
}
