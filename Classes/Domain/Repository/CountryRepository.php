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

use SJBR\StaticInfoTables\Domain\Model\Country;
use SJBR\StaticInfoTables\Domain\Repository\CountryRepository as SJBRCountryRepository;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * A repository for static info tables country
 */
class CountryRepository extends SJBRCountryRepository
{
    /**
     * Returns the class name of this class.
     */
    protected function getRepositoryClassName(): string
    {
        return SJBRCountryRepository::class;
    }

    public function initializeObject(): void
    {
        /** @var Typo3QuerySettings $defaultQuerySettings */
        $defaultQuerySettings = $this->createQuery()->getQuerySettings();
        $defaultQuerySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($defaultQuerySettings);
    }

    public function findByIsoCodeA2(array $isoCodeA2): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching($query->in('isoCodeA2', $isoCodeA2));
        return $query->execute();
    }

    public function findByIsoCodeA3($isoCodeA3): Country
    {
        $query = $this->createQuery();
        $query->matching($query->equals('isoCodeA3', $isoCodeA3));
        /** @var Country $result */
        $result = $query->execute()->getFirst();
        return $result;
    }
}
