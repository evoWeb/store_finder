<?php
namespace Evoweb\StoreFinder\Tests\Unit\Cache;

/***************************************************************
 * Copyright notice
 *
 * (c) 2016 Sebastian Fischer, <typo3@evoweb.de>
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
class AddLocationToCacheTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    /**
     * @var \Evoweb\StoreFinder\Cache\CoordinatesCache|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $coordinatesCache;

    /**
     * Setup for tests
     *
     * @throws \InvalidArgumentException
     * @throws \PHPUnit_Framework_Exception
     * @return void
     */
    public function setUp()
    {
        $this->setupCaches();
    }


    /**
     * Test for something
     *
     * @test
     * @throws \PHPUnit_Framework_Exception
     * @return void
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

        $this->coordinatesCache
            ->expects($this->once())
            ->method('getValueFromCacheTable')
            ->will($this->returnValue($coordinate));

        $fields = ['zipcode', 'city', 'country'];
        $this->coordinatesCache->addCoordinateForAddress($constraint, $fields);

        $fields = ['zipcode', 'city', 'country'];
        $entryIdentifier = $this->coordinatesCache->getHashForAddressWithFields($constraint, $fields);
        $cacheEntry = $this->coordinatesCache->getCoordinateByAddress($constraint);
        $cacheEntry = $this->coordinatesCache->getValueFromCacheTable($entryIdentifier);
        $this->assertEquals($coordinate, $cacheEntry);
    }

    /**
     * Test for something
     *
     * @te st
     * @throws \PHPUnit_Framework_Exception
     * @return void
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

        $GLOBALS['TYPO3_DB']->expects($this->any())
            ->method('exec_SELECTgetSingleRow')
            ->will(self::returnValue([
                'content' => serialize($coordinate),
            ]));

        $fields = ['address', 'zipcode', 'city', 'state', 'country'];
        $this->coordinatesCache->addCoordinateForAddress($constraint, $fields);

        $fields = ['zipcode', 'city', 'country'];
        $entryIdentifier = $this->coordinatesCache->getHashForAddressWithFields($constraint, $fields);
        $this->assertEquals($coordinate, $this->coordinatesCache->getValueFromCacheTable($entryIdentifier));
    }


    /**
     * Test for something
     *
     * @te st
     * @return void
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
     * @te st
     * @return void
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
    protected function getConstraintStub($data)
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

    /**
     * @return void
     */
    protected function setupCaches()
    {
        $this->coordinatesCache = $this
            ->getMockBuilder(\Evoweb\StoreFinder\Cache\CoordinatesCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->coordinatesCache
            ->expects($this->at(0))
            ->method('flushCache');
    }
}
