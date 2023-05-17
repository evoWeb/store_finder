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

use Doctrine\DBAL\ArrayParameterType;
use Evoweb\StoreFinder\Domain\Model\Category;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class CategoryRepository extends Repository
{
    /**
     * @var array
     */
    protected $defaultOrderings = ['sorting' => QueryInterface::ORDER_ASCENDING];

    protected array $settings = [];

    public function __construct(
        protected ConnectionPool $connectionPool
    ) {
        parent::__construct();
    }

    public function setSettings(array $settings): void
    {
        $this->settings = $settings;
    }

    public function initializeObject(): void
    {
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }

    public function findByUids(array $uids): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        $query->matching(
            $query->in('uid', $uids)
        );

        return $query->execute();
    }

    public function findByParent(int $parentUid): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching($query->equals('parent', $parentUid));
        return $query->execute();
    }

    public function findByParentRecursive(array $subCategories, array $categories = []): array
    {
        /** @var Category $subCategory */
        foreach ($subCategories as $subCategory) {
            $categories[] = $subcategoryUid = (int)(is_object($subCategory) ? $subCategory->getUid() : $subCategory);

            $foundCategories = $this->findByParent($subcategoryUid);
            $foundCategories->rewind();

            $categories = $this->findByParentRecursive($foundCategories->toArray(), $categories);
        }

        return array_unique($categories);
    }

    public function getCategories(array $selectedCategories): array
    {
        $categories = $this
            ->getCommonQuery('sys_category', 0, $selectedCategories)
            ->executeQuery()
            ->fetchAllAssociative();

        $pageRepository = $this->getPageRepository();
        foreach ($categories as &$category) {
            $category = $pageRepository->getLanguageOverlay('sys_category', $category);
            $category['children'] = $this->findCategoryByParent($selectedCategories, $category['uid']);
        }

        return $categories;
    }

    protected function findCategoryByParent(array $selectedCategories, int $parentUid): array
    {
        $categoryChildren = $this
            ->getCommonQuery('sys_category', $parentUid, [])
            ->executeQuery()
            ->fetchAllAssociative();

        $pageRepository = $this->getPageRepository();
        foreach ($categoryChildren as &$categoryChild) {
            $categoryChild = $pageRepository->getLanguageOverlay('sys_category', $categoryChild);
            if (in_array($categoryChild['uid'], explode(',', $this->settings['activeCategories']))) {
                $categoryChild['active'] = 1;
            }

            if ($categoryChild['children'] > 0) {
                $categoryChild['children'] = $this->findCategoryByParent($selectedCategories, $categoryChild['uid']);
            }
        }

        return $categoryChildren;
    }

    protected function getCommonQuery(string $table, int $parentUid, array $selectedCategories): QueryBuilder
    {
        $queryBuilder = $this->getQueryBuilderForTable($table);
        /** @var Context $context */
        $context = GeneralUtility::makeInstance(Context::class);
        /** @var LanguageAspect $languageAspect */
        $languageAspect = $context->getAspect('language');
        $expression = $queryBuilder->expr();

        $fields = array_keys($this->settings['tables'][$table]['fields'] ?? ['*' => '']);
        $queryBuilder
            ->select(...$fields)
            ->from($table, 'c')
            ->where(
                $expression->or(
                    $expression->eq('c.parent', $queryBuilder->createNamedParameter($parentUid)),
                    $expression->in(
                        'c.uid',
                        $queryBuilder->createNamedParameter($selectedCategories, ArrayParameterType::INTEGER)
                    )
                ),
                $expression->or(
                    $expression->in('c.sys_language_uid', [0, -1]),
                    $expression->and(
                        $expression->eq('c.l10n_parent', 0),
                        $expression->eq('c.sys_language_uid', $languageAspect->getContentId())
                    )
                ),
            )
            ->innerJoin(
                'c',
                'sys_category_record_mm',
                'mm',
                (string)$expression->and(
                    $expression->eq(
                        'mm.tablenames',
                        $queryBuilder->quote('tx_storefinder_domain_model_location')
                    ),
                    $expression->eq('c.uid', 'mm.uid_local')
                )
            );

        if (!empty($selectedCategories)) {
            $queryBuilder->andWhere(
                $expression->in(
                    'c.uid',
                    $queryBuilder->createNamedParameter($selectedCategories, ArrayParameterType::INTEGER)
                )
            );
        }

        if (!empty($this->settings['storagePid'])) {
            $queryBuilder->andWhere(
                $expression->in('c.pid', GeneralUtility::intExplode(',', $this->settings['storagePid']))
            );
        }

        if (!empty($this->settings['tables'][$table]['sortBy'])) {
            $queryBuilder->addOrderBy(
                $this->settings['tables'][$table]['sortBy']['field'] ?? 'c.uid',
                $this->settings['tables'][$table]['sortBy']['direction'] ?? 'ASC'
            );
        }

        return $queryBuilder;
    }

    protected function getPageRepository(): PageRepository
    {
        return GeneralUtility::makeInstance(PageRepository::class);
    }

    protected function getQueryBuilderForTable(string $table): QueryBuilder
    {
        return $this->connectionPool->getQueryBuilderForTable($table);
    }
}
