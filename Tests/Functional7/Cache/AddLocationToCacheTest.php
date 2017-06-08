<?php
namespace Evoweb\StoreFinder\Tests\Functional7\Cache;

/***************************************************************
 * Copyright notice
 *
 * (c) 2014 Sebastian Fischer, <typo3@evoweb.de>
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

/**
 * Coordinate cache test
 */
class AddLocationToCacheTest extends \TYPO3\CMS\Core\Tests\FunctionalTestCase
{
    /**
     * @var string
     */
    protected $fixturePath = 'typo3conf/ext/store_finder/Tests/Functional/Fixtures/';

    /**
     * @var \Evoweb\StoreFinder\Cache\CoordinatesCache|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $coordinatesCache;

    /**
     * Setup for tests
     */
    public function setUp()
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

        /** @noinspection PhpIncludeInspection */
        $classLoader = require ORIGINAL_ROOT . 'typo3_src/vendor/autoload.php';
        $classLoader->addPsr4('Evoweb\\StoreFinder\\', [realpath(__DIR__ . '/../../../Classes/')]);

        $this->coordinatesCache = new \Evoweb\StoreFinder\Cache\CoordinatesCache($frontendUser, $cacheFrontend);
    }


    /**
     * Test if location only gets stored with zip, city and country in cache table
     *
     * @test
     */
    public function locationWithZipCityCountryOnlyGetStoredInCacheTable()
    {
        $this->coordinatesCache->flushCache();

        $data = [
            'address' => '',
            'zipcode' => substr(mktime(), -5),
            'city' => uniqid('City'),
            'state' => '',
            'country' => uniqid('Country'),
        ];

        $constraint = $this->getConstraintStub($data);
        $coordinate = [
            'latitude' => $constraint->getLatitude(),
            'longitude' => $constraint->getLongitude(),
        ];

        $fields = ['zipcode', 'city', 'country'];
        $this->coordinatesCache->addCoordinateForAddress($constraint, $fields);

        $fields = ['zipcode', 'city', 'country'];
        $hash = $this->coordinatesCache->getHashForAddressWithFields($constraint, $fields);
        $this->assertEquals($coordinate, $this->coordinatesCache->getValueFromCacheTable($hash));
    }

    /**
     * Test for something
     *
     * @te
     */
    public function locationWithAddressZipCityStateCountryGetStoredInCacheTableIfStreetAndStateIsEmpty()
    {
        $this->coordinatesCache->flushCache();

        $data = [
            'address' => '',
            'zipcode' => substr(mktime(), -5),
            'city' => uniqid('City'),
            'state' => '',
            'country' => uniqid('Country'),
        ];

        $constraint = $this->getConstraintStub($data);
        $coordinate = [
            'latitude' => $constraint->getLatitude(),
            'longitude' => $constraint->getLongitude(),
        ];

        $fields = ['address', 'zipcode', 'city', 'state', 'country'];
        $this->coordinatesCache->addCoordinateForAddress($constraint, $fields);

        $fields = ['zipcode', 'city', 'country'];
        $hash = $this->coordinatesCache->getHashForAddressWithFields($constraint, $fields);
        $this->assertEquals($coordinate, $this->coordinatesCache->getValueFromCacheTable($hash));
    }


    /**
     * Test for something
     *
     * @te
     */
    public function locationWithAddressZipCityCountryGetStoredInSessionCache()
    {
        $this->coordinatesCache->flushCache();

        $data = [
            'address' => uniqid('Address'),
            'zipcode' => substr(mktime(), -5),
            'city' => uniqid('City'),
            'state' => '',
            'country' => uniqid('Country'),
        ];

        $constraint = $this->getConstraintStub($data);
        $coordinate = [
            'latitude' => $constraint->getLatitude(),
            'longitude' => $constraint->getLongitude(),
        ];

        $fields = ['address', 'zipcode', 'city', 'state', 'country'];
        $this->coordinatesCache->addCoordinateForAddress($constraint, $fields);

        $fields = ['address', 'zipcode', 'city', 'country'];
        $hash = $this->coordinatesCache->getHashForAddressWithFields($constraint, $fields);
        $this->assertEquals($coordinate, $this->coordinatesCache->getValueFromSession($hash));
    }

    /**
     * Test for something
     *
     * @te
     */
    public function locationWithAddressZipCityStateCountryGetStoredInSessionCache()
    {
        $this->coordinatesCache->flushCache();

        $data = [
            'address' => uniqid('Address'),
            'zipcode' => substr(mktime(), -5),
            'city' => uniqid('City'),
            'state' => '',
            'country' => uniqid('Country'),
        ];

        $constraint = $this->getConstraintStub($data);
        $coordinate = [
            'latitude' => $constraint->getLatitude(),
            'longitude' => $constraint->getLongitude(),
        ];

        $fields = ['address', 'zipcode', 'city', 'state', 'country'];
        $this->coordinatesCache->addCoordinateForAddress($constraint, $fields);

        $fields = ['address', 'zipcode', 'city', 'state', 'country'];
        $hash = $this->coordinatesCache->getHashForAddressWithFields($constraint, $fields);
        $this->assertEquals($coordinate, $this->coordinatesCache->getValueFromSession($hash));
    }


    /**
     * Get a constraint stub
     *
     * @param array $data
     *
     * @return \Evoweb\StoreFinder\Domain\Model\Constraint
     */
    public function getConstraintStub($data)
    {
        $constraint = new \Evoweb\StoreFinder\Domain\Model\Constraint();

        foreach ($data as $field => $value) {
            $setter = 'set' . ucfirst($field);
            if (method_exists($constraint, $setter)) {
                $constraint->{$setter}($value);
            }
        }

        $constraint->setLatitude(51.165691);
        $constraint->setLongitude(10.451526);

        return $constraint;
    }
}
