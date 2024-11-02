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
use Psr\Http\Message\ServerRequestInterface;
use SJBR\StaticInfoTables\Domain\Model\CountryZone;
use TYPO3\CMS\Core\Country\Country;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Country\CountryProvider;
use TYPO3\CMS\Core\Http\Client\GuzzleClientFactory;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Session\UserSessionManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Coordinate cache test
 */
class AddLocationToCacheTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/store_finder',
    ];

    protected array $coreExtensionsToLoad = [
        'extbase',
        'fluid',
    ];

    protected array $configurationToUseInTestInstance = [
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
                    'country' => new Country(
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                    ),
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
                    'country' => new Country(
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                    ),
                ],
                ['address', 'zipcode', 'city', 'state', 'country'],
                ['zipcode', 'city', 'country'],
            ],
            'address zip city country' => [
                [
                    'address' => uniqid('Address'),
                    'zipcode' => substr(time(), -5),
                    'city' => uniqid('City'),
                    'country' => new Country(
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                    ),
                ],
                ['address', 'zipcode', 'city', 'country'],
                ['address', 'zipcode', 'city', 'country'],
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
        $request = $this->createServerRequest('https://typo3-testing.local/typo3/');
        $GLOBALS['TYPO3_REQUEST'] = $request;

        /** @var CacheManager $cacheManager */
        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        $cacheFrontend = $cacheManager->getCache('store_finder_coordinate_cache');

        $userSessionManagerMock = $this->createMock(UserSessionManager::class);
        $coordinatesCache = new CoordinatesCache($cacheFrontend);
        $coordinatesCache->initializeUserSessionManager($userSessionManagerMock);
        $coordinatesCache->flushCache();

        /** @var GuzzleClientFactory $guzzleFactory */
        $guzzleFactory = GeneralUtility::makeInstance(GuzzleClientFactory::class);
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
                    /** @var Country $value */
                    $value = GeneralUtility::makeInstance(CountryProvider::class)->getByAlpha2IsoCode('de');
                }
                if ($field === 'state') {
                    /** @var CountryZone $value */
                    $value->setLocalName('Nordrhein-Westfalen');
                }
                $constraint->{$setter}($value);
            }
        }

        $constraint->setLatitude(51.165691);
        $constraint->setLongitude(10.451526);

        return $constraint;
    }

    private function createServerRequest(string $url, string $method = 'GET'): ServerRequestInterface
    {
        $requestUrlParts = parse_url($url);
        $docRoot = $this->instancePath;

        $serverParams = [
            'DOCUMENT_ROOT' => $docRoot,
            'HTTP_USER_AGENT' => 'TYPO3 Functional Test Request',
            'HTTP_HOST' => $requestUrlParts['host'] ?? 'localhost',
            'SERVER_NAME' => $requestUrlParts['host'] ?? 'localhost',
            'SERVER_ADDR' => '127.0.0.1',
            'REMOTE_ADDR' => '127.0.0.1',
            'SCRIPT_NAME' => '/index.php',
            'PHP_SELF' => '/index.php',
            'SCRIPT_FILENAME' => $docRoot . '/index.php',
            'PATH_TRANSLATED' => $docRoot . '/index.php',
            'QUERY_STRING' => $requestUrlParts['query'] ?? '',
            'REQUEST_URI' => $requestUrlParts['path']
                . (isset($requestUrlParts['query']) ? '?' . $requestUrlParts['query'] : ''),
            'REQUEST_METHOD' => $method,
        ];
        // Define HTTPS and server port
        if (isset($requestUrlParts['scheme'])) {
            if ($requestUrlParts['scheme'] === 'https') {
                $serverParams['HTTPS'] = 'on';
                $serverParams['SERVER_PORT'] = '443';
            } else {
                $serverParams['SERVER_PORT'] = '80';
            }
        }

        // Define a port if used in the URL
        if (isset($requestUrlParts['port'])) {
            $serverParams['SERVER_PORT'] = $requestUrlParts['port'];
        }
        // set up normalizedParams
        $request = new ServerRequest($url, $method, null, [], $serverParams);
        return $request->withAttribute('normalizedParams', NormalizedParams::createFromRequest($request));
    }
}
