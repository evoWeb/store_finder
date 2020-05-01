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

class CategoryRepository extends \TYPO3\CMS\Extbase\Domain\Repository\CategoryRepository
{
    /**
     * @var array
     */
    protected $defaultOrderings = ['sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING];

    public function findByUids(array $uids): \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        $query->matching(
            $query->in('uid', $uids)
        );

        return $query->execute();
    }

    public function findByParentRecursive(array $subCategories, array $categories = []): array
    {
        /** @var \Evoweb\StoreFinder\Domain\Model\Category $subCategory */
        foreach ($subCategories as $subCategory) {
            $categories[] = $subcategoryUid = (int) (is_object($subCategory) ? $subCategory->getUid() : $subCategory);

            /** @noinspection PhpUndefinedMethodInspection */
            /** @var \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult $foundCategories */
            $foundCategories = $this->findByParent($subcategoryUid);
            $foundCategories->rewind();

            $categories = $this->findByParentRecursive($foundCategories->toArray(), $categories);
        }

        return array_unique($categories);
    }
}
