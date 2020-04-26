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

use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * A repository for static info tables country
 */
class CountryRepository extends \SJBR\StaticInfoTables\Domain\Repository\CountryRepository
{
    public function __construct(ObjectManagerInterface $objectManager)
    {
        parent::__construct($objectManager);
        $this->objectType = \SJBR\StaticInfoTables\Domain\Model\Country::class;
    }

    public function findAll(): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        return $query->execute();
    }

    public function findByIsoCodeA2(array $isoCodeA2): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        $query->matching(
            $query->in('isoCodeA2', $isoCodeA2)
        );

        return $query->execute();
    }

    public function findByIsoCodeA3($isoCodeA3): \SJBR\StaticInfoTables\Domain\Model\Country
    {
        /** @var \TYPO3\CMS\Extbase\Persistence\Generic\Query $query */
        $query = $this->createQuery();

        $query->matching($query->equals('isoCodeA3', $isoCodeA3));

        /** @var \SJBR\StaticInfoTables\Domain\Model\Country $result */
        $result = $query->execute()->getFirst();
        return $result;
    }
}
