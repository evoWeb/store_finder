<?php

declare(strict_types=1);

namespace Evoweb\StoreFinder\Domain\Repository;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class ContentRepository
{
    public function __construct(
        protected ConnectionPool $connectionPool
    ) {
    }

    public function findByUid(int $uid): array
    {
        $queryBuilder = $this->getQueryBuilderForTable('tt_content');

        return $queryBuilder
            ->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid))
            )
            ->executeQuery()
            ->fetchAssociative();
    }

    protected function getQueryBuilderForTable(string $table): QueryBuilder
    {
        return $this->connectionPool->getQueryBuilderForTable($table);
    }
}
