<?php

declare(strict_types=1);

namespace Evoweb\StoreFinder\Domain\Repository;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;

class ContentRepository
{
    public function __construct(
        protected ConnectionPool $connectionPool,
        protected FlexFormService $flexFormService,
        protected TypoScriptService $typoScriptService,
    ) {
    }

    public function getPluginSettingsByPluginUid(int $pluginUid): array
    {
        $queryBuilder = $this->getQueryBuilderForTable('tt_content');

        $row = $queryBuilder
            ->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($pluginUid))
            )
            ->executeQuery()
            ->fetchAssociative();

        $settings = $this
            ->flexFormService
            ->convertFlexFormContentToArray($row['pi_flexform'] ?? '')['settings'] ?? [];

        $settings['pid'] = $row['pid'];
        $settings['storagePid'] = $row['pages'];

        $pageTs = BackendUtility::getPagesTSconfig($row['pid']);
        $pageTs = $this->typoScriptService->convertTypoScriptArrayToPlainArray($pageTs);
        $settings += $pageTs['plugin']['tx_storefinder']['ajax'] ?? [];

        return $settings;
    }

    protected function getQueryBuilderForTable(string $table): QueryBuilder
    {
        return $this->connectionPool->getQueryBuilderForTable($table);
    }
}
