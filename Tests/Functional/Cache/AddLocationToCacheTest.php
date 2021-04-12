<?php
namespace Evoweb\StoreFinder\Tests\Functional\Cache;

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

use Evoweb\StoreFinder\Domain\Model\Constraint;
use PHPUnit\Framework\MockObject\MockObject;
use SJBR\StaticInfoTables\Domain\Model\Country;
use SJBR\StaticInfoTables\Domain\Model\CountryZone;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Coordinate cache test
 */
class AddLocationToCacheTest extends \TYPO3\TestingFramework\Core\Functional\FunctionalTestCase
{
    /**
     * @var array
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/store_finder', 'typo3conf/ext/static_info_tables'];

    /**
     * @var array
     */
    protected $coreExtensionsToLoad = ['extbase', 'fluid'];

    /**
     * @var \Evoweb\StoreFinder\Cache\CoordinatesCache|MockObject
     */
    protected $coordinatesCache;

    /**
     * @var \Evoweb\StoreFinder\Service\GeocodeService
     */
    protected $geocodeService;

    public function setUp(): void
    {
        parent::setUp();

        $frontendUser = new \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication();

        $cacheManager = new \TYPO3\CMS\Core\Cache\CacheManager();
        $cacheManager->setCacheConfigurations([
            'store_finder_coordinate' => [
                'groups' => ['system'],
            ]
        ]);
        $cacheFrontend = $cacheManager->getCache('store_finder_coordinate');

        $this->createCacheTables($cacheFrontend);

        $this->coordinatesCache = new \Evoweb\StoreFinder\Cache\CoordinatesCache($cacheFrontend, $frontendUser);

        /** @var \Evoweb\StoreFinder\Domain\Repository\CountryRepository $categoryRepository */
        $categoryRepository = GeneralUtility::makeInstance(
            \Evoweb\StoreFinder\Domain\Repository\CountryRepository::class
        );

        $this->geocodeService = new \Evoweb\StoreFinder\Service\GeocodeService(
            $this->coordinatesCache,
            $categoryRepository
        );
    }

    public function cacheDataProvider(): array
    {
        return [
            'zip city country only' => [
                [
                    'address' => '',
                    'zipcode' => substr(time(), -5),
                    'city' => uniqid('City'),
                    'state' => null,
                    'country' => GeneralUtility::makeInstance(Country::class),
                ],
                ['zipcode', 'city', 'country'],
                ['zipcode', 'city', 'country'],
            ],
            'address zip city state country if street and state empty' => [
                [
                    'address' => '',
                    'zipcode' => substr(time(), -5),
                    'city' => uniqid('City'),
                    'state' => null,
                    'country' => GeneralUtility::makeInstance(Country::class),
                ],
                ['address', 'zipcode', 'city', 'state', 'country'],
                ['zipcode', 'city', 'country'],
            ],
            'address zip city country' => [
                [
                    'address' => uniqid('Address'),
                    'zipcode' => substr(time(), -5),
                    'city' => uniqid('City'),
                    'state' => null,
                    'country' => GeneralUtility::makeInstance(Country::class),
                ],
                ['address', 'zipcode', 'city', 'state', 'country'],
                ['address', 'zipcode', 'city', 'country'],
            ],
            'address zip city state country' => [
                [
                    'address' => uniqid('Address'),
                    'zipcode' => substr(time(), -5),
                    'city' => uniqid('City'),
                    'state' => GeneralUtility::makeInstance(CountryZone::class),
                    'country' => GeneralUtility::makeInstance(Country::class),
                ],
                ['address', 'zipcode', 'city', 'state', 'country'],
                ['address', 'zipcode', 'city', 'state', 'country'],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider cacheDataProvider
     *
     * @param array $data
     * @param array $addFields
     * @param array $getFields
     */
    public function locationStoredInCacheTable(array $data, array $addFields, array $getFields)
    {
        $this->coordinatesCache->flushCache();

        $constraint = $this->getConstraintStub($data);

        $queryValues = $this->geocodeService->prepareValuesForQuery($constraint, $addFields);
        $this->coordinatesCache->addCoordinateForAddress($constraint, $queryValues);

        $queryValues = $this->geocodeService->prepareValuesForQuery($constraint, $getFields);
        $this->assertEquals($constraint, $this->coordinatesCache->getCoordinateByAddress($constraint, $queryValues));
    }

    public function getConstraintStub(array $data): Constraint
    {
        $constraint = new Constraint();

        foreach ($data as $field => $value) {
            $setter = 'set' . ucfirst($field);
            if (method_exists($constraint, $setter) && !empty($value)) {
                if ($field === 'country') {
                    $value->setIsoCodeA2('de');
                }
                if ($field === 'state') {
                    $value->setLocalName('Nordrhein-Westfalen');
                }
                $constraint->{$setter}($value);
            }
        }

        $constraint->setLatitude(51.165691);
        $constraint->setLongitude(10.451526);

        return $constraint;
    }

    protected function createCacheTables(\TYPO3\CMS\Core\Cache\Frontend\FrontendInterface $cacheFrontend)
    {
        /** @var \TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend $cacheBackend */
        $cacheBackend = $cacheFrontend->getBackend();

        $cacheTableSql = file_get_contents(
            ExtensionManagementUtility::extPath('core') .
            'Resources/Private/Sql/Cache/Backend/Typo3DatabaseBackendCache.sql'
        );
        $requiredTableStructures = str_replace('###CACHE_TABLE###', $cacheBackend->getCacheTable(), $cacheTableSql);
        $tagsTableSql = file_get_contents(
            ExtensionManagementUtility::extPath('core') .
            'Resources/Private/Sql/Cache/Backend/Typo3DatabaseBackendTags.sql'
        );
        $requiredTagTableStructures = str_replace('###TAGS_TABLE###', $cacheBackend->getTagsTable(), $tagsTableSql);

        /** @var \TYPO3\CMS\Core\Database\Schema\SchemaMigrator $schemaMigrator */
        $schemaMigrator = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Database\Schema\SchemaMigrator::class
        );
        $schemaMigrator->install([$requiredTableStructures]);
        $schemaMigrator->install([$requiredTagTableStructures]);
    }
}
