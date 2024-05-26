<?php

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

namespace Evoweb\StoreFinder\Tests\Functional\Cache;

use Evoweb\StoreFinder\Cache\CoordinatesCache;
use Evoweb\StoreFinder\Domain\Model\Constraint;
use Evoweb\StoreFinder\Service\GeocodeService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\NullLogger;
use SJBR\StaticInfoTables\Domain\Model\Country;
use SJBR\StaticInfoTables\Domain\Model\CountryZone;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Client\GuzzleClientFactory;
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
        'extensionmanager',
    ];

    protected array $configurationToUseInTestInstance = [
        'EXTENSIONS' => [
            'static_info_tables' => [
                'enableManager' => false,
            ],
        ],
        'SYS' => [
            'caching' => [
                'cacheConfigurations' => [
                    'store_finder_coordinate_cache' => [
                        'groups' => ['system'],
                    ],
                ],
            ],
        ],
    ];

    public static function cacheDataProvider(): array
    {
        return [
            'zip city country only' => [
                [
                    'address' => '',
                    'zipcode' => substr(time(), -5),
                    'city' => uniqid('City'),
                    'state' => null,
                    'country' => new Country(),
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
                    'country' => new Country(),
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
                    'country' => new Country(),
                ],
                ['address', 'zipcode', 'city', 'state', 'country'],
                ['address', 'zipcode', 'city', 'country'],
            ],
            'address zip city state country' => [
                [
                    'address' => uniqid('Address'),
                    'zipcode' => substr(time(), -5),
                    'city' => uniqid('City'),
                    'state' => new CountryZone(),
                    'country' => new Country(),
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
        $expected = $this->getConstraintStub($data);
        $actual = unserialize(serialize($expected));

        /** @var ServerRequestInterface $request */
        $request = new ServerRequest(
            '/',
            'POST',
            'php://input',
            [],
            [],
            null,
        );

        $frontendUser = new FrontendUserAuthentication();
        $frontendUser->setLogger(new NullLogger());
        $frontendUser->start($request);

        $request = $request->withAttribute('frontend.user', $frontendUser);
        $GLOBALS['TYPO3_REQUEST'] = $request;

        /** @var CacheManager $cacheManager */
        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        /** @var GuzzleClientFactory $guzzleFactory */
        $guzzleFactory = GeneralUtility::makeInstance(GuzzleClientFactory::class);
        $cacheFrontend = $cacheManager->getCache('store_finder_coordinate_cache');

        $coordinatesCache = new CoordinatesCache($cacheFrontend, $frontendUser);
        $coordinatesCache->flushCache();

        $geocodeService = new GeocodeService($coordinatesCache, $guzzleFactory);

        $queryValues = $geocodeService->prepareValuesForQuery($expected, $addFields);
        $coordinatesCache->addCoordinateForAddress($expected, $queryValues);

        $queryValues = $geocodeService->prepareValuesForQuery($expected, $getFields);
        $actual = $coordinatesCache->getCoordinateByAddress($actual, $queryValues);
        self::assertEquals($expected, $actual);
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
}
