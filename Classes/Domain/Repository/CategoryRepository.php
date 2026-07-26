<?php

declare(strict_types=1);

/*
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Evoweb\StoreFinder\Domain\Repository;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Exception;
use Evoweb\StoreFinder\Domain\Model\Category;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException as AspectNotFoundException;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * A repository for categories
 *
 * @extends Repository<Category>
 */
class CategoryRepository extends Repository
{
    protected $defaultOrderings = ['sorting' => QueryInterface::ORDER_ASCENDING];

    /**
     * @var array<string, mixed>
     */
    protected array $settings = [];

    public function __construct(
        protected ConnectionPool $connectionPool
    ) {
        parent::__construct();
    }

    /**
     * @param array<string, mixed> $settings
     */
    public function setSettings(array $settings): void
    {
        $this->settings = $settings;
    }

    public function initializeObject(): void
    {
        /** @var Typo3QuerySettings $defaultQuerySettings */
        $defaultQuerySettings = $this->createQuery()->getQuerySettings();
        $defaultQuerySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($defaultQuerySettings);
    }

    /**
     * @param int[] $uids
     * @return QueryResultInterface<int, Category>
     * @throws InvalidQueryException
     */
    public function findByUids(array $uids): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        $query->matching(
            $query->in('uid', $uids)
        );

        return $query->execute();
    }

    /**
     * @return QueryResultInterface<int, Category>
     */
    public function findByParent(int $parentUid): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching($query->equals('parent', $parentUid));
        return $query->execute();
    }

    /**
     * @param int[] $categories
     * @param int[] $result
     * @return int[]
     */
    public function findByParentRecursive(array $categories, array $result = []): array
    {
        foreach ($categories as $category) {
            $result[] = $category;

            $foundCategories = $this->findByParent($category)->toArray();
            $foundCategoriesUid = array_map(fn(Category $category): int => $category->getUid() ?? 0, $foundCategories);

            $result = $this->findByParentRecursive($foundCategoriesUid, $result);
        }

        return array_unique($result);
    }

    /**
     * @param int[] $selectedCategories
     * @return array<array<string, mixed>>
     * @throws Exception
     * @throws AspectNotFoundException
     */
    public function getCategoriesByParents(array $selectedCategories): array
    {
        $categories = $this
            ->getCommonQuery('sys_category', $selectedCategories)
            ->executeQuery()
            ->fetchAllAssociative();

        $pageRepository = $this->getPageRepository();
        $result = [];
        foreach ($categories as $category) {
            $category = $pageRepository->getLanguageOverlay('sys_category', $category);
            if ($category === null) {
                continue;
            }

            $category['children'] = $this->findCategoryByParent($selectedCategories, $category['uid']);
            $result[] = $category;
        }

        return $result;
    }

    /**
     * @param int[] $selectedCategories
     * @return array<array<string, mixed>>
     * @throws Exception
     * @throws AspectNotFoundException
     */
    protected function findCategoryByParent(array $selectedCategories, int $parentUid): array
    {
        $queryBuilder = $this->getCommonQuery('sys_category', []);
        $categoryChildren = $queryBuilder
            ->andWhere($queryBuilder->expr()->eq(
                'parent',
                $queryBuilder->createNamedParameter($parentUid, Connection::PARAM_INT)
            ))
            ->executeQuery()
            ->fetchAllAssociative();

        $pageRepository = $this->getPageRepository();
        $activeCategories = explode(',', $this->toStringOrDefault($this->settings['activeCategories'] ?? ''));

        $result = [];
        foreach ($categoryChildren as $categoryChild) {
            $categoryChild = $pageRepository->getLanguageOverlay('sys_category', $categoryChild);
            if ($categoryChild === null) {
                continue;
            }

            if (in_array($categoryChild['uid'], $activeCategories)) {
                $categoryChild['active'] = 1;
            }

            if ($categoryChild['children'] > 0) {
                $categoryChild['children'] = $this->findCategoryByParent($selectedCategories, $categoryChild['uid']);
            }

            $result[] = $categoryChild;
        }

        return $result;
    }

    /**
     * @param int[] $selectedCategories
     * @throws AspectNotFoundException
     */
    protected function getCommonQuery(string $table, array $selectedCategories): QueryBuilder
    {
        $queryBuilder = $this->getQueryBuilderForTable($table);
        /** @var Context $context */
        $context = GeneralUtility::makeInstance(Context::class);
        /** @var LanguageAspect $languageAspect */
        $languageAspect = $context->getAspect('language');
        $expression = $queryBuilder->expr();

        /** @var array<string, mixed> $tables */
        // @extensionScannerIgnoreLine
        $tables = $this->settings['tables'] ?? [];
        /** @var array<string, mixed> $tableSettings */
        $tableSettings = $tables[$table] ?? [];
        /** @var array<string, mixed> $fieldsSetting */
        $fieldsSetting = $tableSettings['fields'] ?? ['*' => ''];
        $fields = array_keys($fieldsSetting);
        $fields[] = 'uid';
        $fields[] = 'pid';
        /** @var array<string, mixed> $tca */
        $tca = $GLOBALS['TCA'] ?? [];
        /** @var array<string, mixed> $tableTca */
        $tableTca = $tca[$table] ?? [];
        /** @var array<string, mixed> $tableTcaCtrl */
        $tableTcaCtrl = $tableTca['ctrl'] ?? [];
        $fields[] = $this->toStringOrDefault($tableTcaCtrl['languageField'] ?? '');
        $queryBuilder
            ->select(...$fields)
            ->from($table, 'c')
            ->where(
                $expression->or(
                    $expression->in('c.sys_language_uid', [0, -1]),
                    $expression->and(
                        $expression->eq('c.l10n_parent', 0),
                        $expression->eq('c.sys_language_uid', $languageAspect->getContentId())
                    )
                ),
            )
            ->groupBy('c.uid');

        if (!empty($selectedCategories)) {
            $queryBuilder->andWhere(
                $expression->in(
                    'c.uid',
                    $queryBuilder->createNamedParameter($selectedCategories, ArrayParameterType::INTEGER)
                )
            );
        }

        /** @var array<string, mixed> $sortBySetting */
        $sortBySetting = $tableSettings['sortBy'] ?? [];
        if (!empty($sortBySetting)) {
            $queryBuilder->addOrderBy(
                $this->toStringOrDefault($sortBySetting['field'] ?? 'c.uid'),
                $this->toStringOrDefault($sortBySetting['direction'] ?? 'ASC')
            );
        }

        return $queryBuilder;
    }

    protected function getPageRepository(): PageRepository
    {
        return GeneralUtility::makeInstance(PageRepository::class);
    }

    /**
     * @param int[] $categories
     * @return int[]
     * @throws Exception
     */
    public function enrichCategoriesWithChildren(array $categories): array
    {
        $result = $categories;
        foreach ($categories as $category) {
            $queryBuilder = $this->getQueryBuilderForTable('sys_category');
            $columnValues = $queryBuilder
                ->select('uid')
                ->from('sys_category')
                ->where($queryBuilder->expr()->eq('parent', $category))
                ->executeQuery()
                ->fetchFirstColumn();
            $children = array_map(
                static fn(mixed $value): int => is_numeric($value) ? (int)$value : 0,
                $columnValues
            );
            $children = $this->enrichCategoriesWithChildren($children);
            $result = array_merge($result, $children);
        }
        return $result;
    }

    protected function getQueryBuilderForTable(string $table): QueryBuilder
    {
        return $this->connectionPool->getQueryBuilderForTable($table);
    }

    protected function toStringOrDefault(mixed $value, string $default = ''): string
    {
        return is_scalar($value) ? (string)$value : $default;
    }
}
