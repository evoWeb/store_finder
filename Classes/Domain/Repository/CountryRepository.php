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

use SJBR\StaticInfoTables\Domain\Model\Country;
use SJBR\StaticInfoTables\Domain\Repository\CountryRepository as ExtbaseCountryRepository;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * A repository for static info tables country
 */
class CountryRepository extends ExtbaseCountryRepository
{
    /**
     * Returns the class name of this class.
     *
     * @return string Class name of the repository.
     */
    protected function getRepositoryClassName()
    {
        return ExtbaseCountryRepository::class;
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

    public function findByIsoCodeA3($isoCodeA3): Country
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        $query->matching(
            $query->equals('isoCodeA3', $isoCodeA3)
        );

        /** @var Country $result */
        $result = $query->execute()->getFirst();
        return $result;
    }
}
