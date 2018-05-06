<?php
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

/**
 * A repository for static info tables country
 */
class CountryRepository extends \SJBR\StaticInfoTables\Domain\Repository\CountryRepository
{
    /**
     * Constructs a new Repository
     *
     * @param \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager
     */
    public function __construct(\TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager)
    {
        parent::__construct($objectManager);
        $this->objectType = \SJBR\StaticInfoTables\Domain\Model\Country::class;
    }

    public function findAll(): \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        return $query->execute();
    }

    public function findByIsoCodeA2(array $isoCodeA2): \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        $query->matching(
            $query->in('isoCodeA2', $isoCodeA2)
        );

        return $query->execute();
    }
}
