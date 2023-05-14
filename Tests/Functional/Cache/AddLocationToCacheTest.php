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

use Evoweb\StoreFinder\Cache\CoordinatesCache;
use Evoweb\StoreFinder\Domain\Model\Constraint;
use Evoweb\StoreFinder\Domain\Repository\CountryRepository;
use Evoweb\StoreFinder\Service\GeocodeService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\NullLogger;
use SJBR\StaticInfoTables\Domain\Model\Country;
use SJBR\StaticInfoTables\Domain\Model\CountryZone;
use TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Database\Schema\SchemaMigrator;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Coordinate cache test
 */
class AddLocationToCacheTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/store_finder',
        'typo3conf/ext/static_info_tables',
    ];

    protected array $coreExtensionsToLoad = [
        'extbase',
        'fluid',
    ];

    protected array $configurationToUseInTestInstance = [
        'EXTENSIONS' => [
            'static_info_tables' => [
                'enableManager' => false,
            ]
        ]
    ];

    protected CoordinatesCache|MockObject $coordinatesCache;

    protected GeocodeService $geocodeService;

    public function setUp(): void
    {
        parent::setUp();

        $logger = new NullLogger();

        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $frontendUser = new FrontendUserAuthentication();
        $frontendUser->setLogger($logger);
        $frontendUser->start($request);

        $cacheManager = new CacheManager();
        $cacheManager->setCacheConfigurations([
            'store_finder_coordinate' => [
                'groups' => ['system'],
            ]
        ]);
        $cacheFrontend = $cacheManager->getCache('store_finder_coordinate');

        $this->createCacheTables($cacheFrontend);

        $this->coordinatesCache = new CoordinatesCache($cacheFrontend, $frontendUser);

        /** @var CountryRepository $categoryRepository */
        $categoryRepository = GeneralUtility::makeInstance(CountryRepository::class);

        $this->geocodeService = new GeocodeService($this->coordinatesCache, $categoryRepository);
    }

    public static function cacheDataProvider(): array
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

    #[Test]
    #[DataProvider('cacheDataProvider')]
    public function locationStoredInCacheTable(array $data, array $addFields, array $getFields)
    {
        $this->coordinatesCache->flushCache();

        $constraint = $this->getConstraintStub($data);

        $queryValues = $this->geocodeService->prepareValuesForQuery($constraint, $addFields);
        $this->coordinatesCache->addCoordinateForAddress($constraint, $queryValues);

        $queryValues = $this->geocodeService->prepareValuesForQuery($constraint, $getFields);
        self::assertEquals($constraint, $this->coordinatesCache->getCoordinateByAddress($constraint, $queryValues));
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

    protected function createCacheTables(FrontendInterface $cacheFrontend)
    {
        /** @var Typo3DatabaseBackend $cacheBackend */
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

        /** @var SchemaMigrator $schemaMigrator */
        $schemaMigrator = GeneralUtility::makeInstance(SchemaMigrator::class);
        $schemaMigrator->install([$requiredTableStructures]);
        $schemaMigrator->install([$requiredTagTableStructures]);
    }
}
