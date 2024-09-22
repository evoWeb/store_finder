<?php

declare(strict_types=1);

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

namespace Evoweb\StoreFinder\Domain\Repository;

use Doctrine\DBAL\ArrayParameterType;
use Evoweb\StoreFinder\Domain\Model\Constraint;
use Evoweb\StoreFinder\Domain\Model\Location;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
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
        $query
            ->getQuerySettings()
                ->setIgnoreEnableFields(true)
                ->setRespectStoragePage(false)
                ->setRespectSyslanguage(false);

        /** @var Location $location */
        $location = $query
            ->matching($query->equals('uid', $uid))
                ->execute()
                    ->getFirst();

        return $location;
    }

    public function findOneByUid(int $uid): ?Location
    {
        /** @var Query $query */
        $query = $this->createQuery();
        $query
            ->getQuerySettings()
                ->setRespectStoragePage(false);

        /** @var Location $location */
        $location = $query
            ->matching($query->equals('uid', $uid))
                ->execute()
                    ->getFirst();

        return $location;
    }

    public function findByConstraint(Constraint $constraint, bool $raw = false): array
    {
        if (!$constraint->isGeocoded()) {
            return [];
        }

        /** @var Query $query */
        $query = $this->createQuery();

        $storagePid = $query
            ->getQuerySettings()
                ->getStoragePageIds();

        $tableName = 'tx_storefinder_domain_model_location';
        $queryBuilder = $this->getQueryBuilderForTable($tableName);
        $expression = $queryBuilder->expr();

        $queryBuilder
            ->from($tableName, 'l')
            ->distinct()
            ->select('l.*')
            ->selectLiteral(
                '(acos(
                    sin(' . $constraint->getLatitude() * M_PI . ' / 180) *
                    sin(latitude * ' . M_PI . ' / 180) +
                    cos(' . $constraint->getLatitude() * M_PI . ' / 180) *
                    cos(latitude * ' . M_PI . ' / 180) *
                    cos((' . $constraint->getLongitude() . ' - longitude) * ' . M_PI . ' / 180)
                ) * 6370) as `distance`'
            )
            ->where(
                $expression->in(
                    'l.pid',
                    $queryBuilder->createNamedParameter($storagePid, ArrayParameterType::INTEGER)
                )
            )
            ->orderBy('distance');

        $queryBuilder = $this->addCountryQueryPart($constraint, $queryBuilder);
        $queryBuilder = $this->addCategoryQueryPart($constraint, $queryBuilder);
        $queryBuilder = $this->addAttributeQueryPart($constraint, $queryBuilder);
        $queryBuilder = $this->addRadiusQueryPart($constraint, $queryBuilder);
        $queryBuilder = $this->addLimitQueryParts($constraint, $queryBuilder);
        $queryBuilder = $this->addFulltextSearchQueryParts($constraint, $queryBuilder);
        $queryBuilder = $this->addLanguagePart($tableName, 'l', $queryBuilder);

        $query->statement($queryBuilder);

        return $query->execute($raw)->toArray();
    }

    protected function addCountryQueryPart(Constraint $constraint, QueryBuilder $queryBuilder): QueryBuilder
    {
        if (!$constraint->getCountry()) {
            return $queryBuilder;
        }

        $expression = $queryBuilder->expr();

        $queryBuilder->andWhere(
            $expression->eq(
                'l.country',
                $queryBuilder->createNamedParameter($constraint->getCountry()->getAlpha2IsoCode())
            )
        );

        return $queryBuilder;
    }

    protected function addCategoryQueryPart(Constraint $constraint, QueryBuilder $queryBuilder): QueryBuilder
    {
        if (
            ($this->settings['categoryPriority'] == 'limitResultsToCategories')
            || (
                $this->settings['categoryPriority'] == 'useSelectedCategoriesIfNoFilterSelected'
                && !count($constraint->getCategory())
            )
        ) {
            $constraint->setCategory(GeneralUtility::intExplode(',', $this->settings['categories'], true));
        }
        $categories = $this->categoryRepository->findByParentRecursive($constraint->getCategory());

        if (empty($categories)) {
            return $queryBuilder;
        }

        $expression = $queryBuilder->expr();

        $queryBuilder
            ->innerJoin(
                'l',
                'sys_category_record_mm',
                'mm',
                (string)$expression->and(
                    $expression->eq('l.uid', 'mm.uid_foreign'),
                    $expression->eq(
                        'mm.tablenames',
                        $queryBuilder->quote('tx_storefinder_domain_model_location')
                    ),
                    $expression->eq(
                        'mm.fieldname',
                        $queryBuilder->quote('categories')
                    ),
                )
            )
            ->andWhere(
                $expression->in(
                    'mm.uid_local',
                    $queryBuilder->createNamedParameter($categories, ArrayParameterType::INTEGER)
                )
            )
            ->addSelectLiteral('GROUP_CONCAT(mm.uid_local) as categories');

        return $queryBuilder;
    }

    protected function addAttributeQueryPart(Constraint $constraint, QueryBuilder $queryBuilder): QueryBuilder
    {
        if (!$constraint->getAttributes()->count()) {
            return $queryBuilder;
        }

        $expression = $queryBuilder->expr();

        $queryBuilder->innerJoin(
            'l',
            'tx_storefinder_location_attribute_mm',
            'a',
            (string)$expression->and(
                $expression->eq('l.uid', 'a.uid_foreign'),
                $expression->eq(
                    'a.tablenames',
                    $queryBuilder->createNamedParameter('tx_storefinder_domain_model_attribute')
                ),
                $expression->eq(
                    'a.fieldname',
                    $queryBuilder->createNamedParameter('attributes')
                )
            )
        );

        $fieldName = 'a.uid_foreign';
        $constraints = [
            $expression->isNull($fieldName),
            $expression->eq($fieldName, $expression->literal('')),
            $expression->eq($fieldName, $expression->literal('0')),
        ];
        foreach ($constraint->getAttributes() as $attribute) {
            $constraints[] = $expression->inSet(
                $fieldName,
                $expression->literal((string)$attribute->getUid())
            );
        }
        $queryBuilder->andWhere($expression->or(...$constraints));

        return $queryBuilder;
    }

    protected function addRadiusQueryPart(Constraint $constraint, QueryBuilder $queryBuilder): QueryBuilder
    {
        if ($this->settings['distanceUnit'] == 'miles') {
            // convert miles into kilometers as the calculation delivers kilometer results
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

        // Select all entries for the current language
        // If any language is set -> get those entries which are not translated yet
        $languageField = $GLOBALS['TCA'][$tableName]['ctrl']['languageField'];

        /** @var Context $context */
        $context = GeneralUtility::makeInstance(Context::class);
        /** @var LanguageAspect $languageAspect */
        $languageAspect = $context->getAspect('language');

        $transOrigPointerField = $GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField'] ?? '';
        if (
            !$transOrigPointerField
            || !$languageAspect->getContentId()
            || !$languageAspect->doOverlays()
        ) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->in(
                    $tableAlias . '.' . $languageField,
                    [$languageAspect->getContentId(), -1]
                )
            );
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
        // Records in translation with no default language
        if ($languageAspect->getOverlayType() === LanguageAspect::OVERLAYS_ON_WITH_FLOATING) {
            $andConditions[] = $queryBuilder->expr()->and(
                $queryBuilder->expr()->eq($tableAlias . '.' . $languageField, $languageAspect->getContentId()),
                $queryBuilder->expr()->eq($tableAlias . '.' . $transOrigPointerField, 0),
                $queryBuilder->expr()->notIn(
                    $tableAlias . '.' . $transOrigPointerField,
                    $defaultLanguageRecordsSubSelect->getSQL()
                )
            );
        }
        if ($languageAspect->getOverlayType() === LanguageAspect::OVERLAYS_MIXED) {
            // returns records from current language which have a default language
            // together with not translated default language records
            $translatedOnlyTableAlias = $tableAlias . '_to';
            $queryBuilderForSubselect = $queryBuilder->getConnection()->createQueryBuilder();
            $queryBuilderForSubselect
                ->select($translatedOnlyTableAlias . '.' . $transOrigPointerField)
                ->from($tableName, $translatedOnlyTableAlias)
                ->where(
                    $queryBuilderForSubselect->expr()->and(
                        $queryBuilderForSubselect->expr()->gt(
                            $translatedOnlyTableAlias . '.' . $transOrigPointerField,
                            0
                        ),
                        $queryBuilderForSubselect->expr()->eq(
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
                    $queryBuilderForSubselect->getSQL()
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
        $search = preg_replace('/[^a-zA-Z0-9äöüÄÖÜß,-]+/', '', $constraint->getSearch());
        if (
            $search
            && isset($this->settings['fulltextSearchFields'])
            && is_array($this->settings['fulltextSearchFields'])
        ) {
            $expression = $queryBuilder->expr();

            $fullTextSearchConstraint = [];
            $searchWordWrap = $this->settings['fulltextSearchWordWrap'] ?? '|';

            $searchWords = GeneralUtility::trimExplode(',', $search);
            foreach ($searchWords as $searchWord) {
                foreach ($this->settings['fulltextSearchFields'] as $searchField) {
                    $fullTextSearchConstraint[] = $expression->like(
                        $searchField,
                        $queryBuilder->createNamedParameter(str_replace('|', $searchWord, $searchWordWrap))
                    );
                }
            }

            if (count($fullTextSearchConstraint)) {
                $queryBuilder->andWhere($expression->or(...$fullTextSearchConstraint));
            }
        }

        return $queryBuilder;
    }

    public function findCenterByLatitudeAndLongitude(): Location
    {
        /** @var Query $query */
        $query = $this->createQuery();

        $query->setOrderings(['latitude' => QueryInterface::ORDER_ASCENDING]);
        /** @var ?Location $minLatitude south */
        $minLatitude = $query->execute()->getFirst();

        // only search for the other locations if first succeeded otherwise we have no locations at all
        if ($minLatitude === null) {
            $maxLatitude = $minLongitude = $maxLongitude = null;
        } else {
            $query->setOrderings(['latitude' => QueryInterface::ORDER_DESCENDING]);
            /** @var ?Location $maxLatitude north */
            $maxLatitude = $query->execute()->getFirst();

            $query->setOrderings(['longitude' => QueryInterface::ORDER_ASCENDING]);
            /** @var ?Location $minLongitude west */
            $minLongitude = $query->execute()->getFirst();

            $query->setOrderings(['longitude' => QueryInterface::ORDER_DESCENDING]);
            /** @var ?Location $maxLongitude east */
            $maxLongitude = $query->execute()->getFirst();
        }

        /** @var Location $location */
        $location = GeneralUtility::makeInstance(Location::class);
        $latitudeZoom = $longitudeZoom = 0;

        /**
         * http://stackoverflow.com/questions/6048975
         *    /google-maps-v3-how-to-calculate-the-zoom-level-for-a-given-bounds
         */
        if ($minLatitude instanceof Location && $maxLatitude instanceof Location) {
            $location->setLatitude(($maxLatitude->getLatitude() + $minLatitude->getLatitude()) / 2);
            $latitudeDiff = $this->latRad($maxLatitude->getLatitude()) - $this->latRad($minLatitude->getLatitude());
            $latitudeFraction = ($latitudeDiff) / M_PI;
            $latitudeZoom = $this->zoom($this->settings['mapSize']['height'], self::GLOBE_WIDTH, $latitudeFraction);
        }

        if ($minLongitude instanceof Location && $maxLongitude instanceof Location) {
            $location->setLongitude(($maxLongitude->getLongitude() + $minLongitude->getLongitude()) / 2);
            $longitudeDiff = $maxLongitude->getLongitude() - $minLongitude->getLongitude();
            $longitudeFraction = ($longitudeDiff < 0 ? $longitudeDiff + 360 : $longitudeDiff) / 360;
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

        /** @var Location $location */
        $location = $query
            ->matching($query->equals('center', 1))
                ->execute()
                    ->getFirst();

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
     */
    protected function zoom(int $mapPx, int $worldPx, float $fraction): float
    {
        return floor(log($mapPx / $worldPx / $fraction) / self::MATH_LN2);
    }

    /**
     * Query location repository for all locations that
     * have latitude or longitude empty or geocode set to 1
     */
    public function findAllWithoutLatLon(int $limit = 500): array
    {
        /** @var Query $query */
        $query = $this->createQuery();
        $query
            ->getQuerySettings()
                ->setRespectStoragePage(false);

        $query
            ->setLimit($limit)
            ->matching(
                $query->logicalOr(
                    $query->equals('geocode', 1),
                    $query->logicalOr(
                        $query->equals('latitude', 0),
                        $query->equals('longitude', 0)
                    )
                )
            );

        return $query->execute()->toArray();
    }

    public function getLocations(Constraint $constraint): array
    {
        $table = 'tx_storefinder_domain_model_location';
        $queryBuilder = $this->getQueryBuilderForTable($table);
        $expression = $queryBuilder->expr();

        $fields = array_keys($this->settings['tables'][$table]['fields'] ?? ['*' => '']);
        $queryBuilder
            ->select(...$fields)
            ->from($table, 'l')
            ->groupBy('l.uid');

        if (!empty($constraint->getCategory())) {
            $queryBuilder
                ->innerJoin(
                    'l',
                    'sys_category_record_mm',
                    'mm',
                    (string)$expression->and(
                        $expression->eq('l.uid', 'mm.uid_foreign'),
                        $expression->eq(
                            'mm.tablenames',
                            $queryBuilder->quote('tx_storefinder_domain_model_location')
                        ),
                        $expression->eq(
                            'mm.fieldname',
                            $queryBuilder->quote('categories')
                        ),
                    )
                )
                ->andWhere(
                    $expression->in(
                        'mm.uid_local',
                        $queryBuilder->createNamedParameter($constraint->getCategory(), ArrayParameterType::INTEGER)
                    )
                )
                ->addSelectLiteral('GROUP_CONCAT(mm.uid_local) as categories');
        }

        if ($constraint->isGeocoded()) {
            $queryBuilder
                ->addSelectLiteral(
                    '(acos(
                        sin(' . $constraint->getLatitude() * M_PI . ' / 180) *
                        sin(latitude * ' . M_PI . ' / 180) +
                        cos(' . $constraint->getLatitude() * M_PI . ' / 180) *
                        cos(latitude * ' . M_PI . ' / 180) *
                        cos((' . $constraint->getLongitude() . ' - longitude) * ' . M_PI . ' / 180)
                    ) * 6370) as `distance`'
                )
                ->addOrderBy('distance');
        }

        $queryBuilder = $this->addFulltextSearchQueryParts($constraint, $queryBuilder);
        $queryBuilder = $this->addLanguagePart($table, 'l', $queryBuilder);

        if (!empty($this->settings['storagePid'])) {
            $queryBuilder->andWhere(
                $expression->in('l.pid', GeneralUtility::intExplode(',', $this->settings['storagePid']))
            );
        }

        if (!empty($this->settings['tables'][$table]['sortBy'])) {
            $queryBuilder->addOrderBy(
                $this->settings['tables'][$table]['sortBy']['field'] ?? 'c.uid',
                $this->settings['tables'][$table]['sortBy']['direction'] ?? 'ASC'
            );
        }

        /** @var array[] $locations */
        $locations = $queryBuilder
            ->executeQuery()
            ->fetchAllAssociative();

        $pageRepository = $this->getPageRepository();
        foreach ($locations as &$location) {
            $location = $pageRepository->getLanguageOverlay('tx_storefinder_domain_model_location', $location);
        }

        return $locations;
    }

    protected function getPageRepository(): PageRepository
    {
        return GeneralUtility::makeInstance(PageRepository::class);
    }

    protected function getQueryBuilderForTable(string $table): QueryBuilder
    {
        return $this->connectionPool->getQueryBuilderForTable($table);
    }
}
