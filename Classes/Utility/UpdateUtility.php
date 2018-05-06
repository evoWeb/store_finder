<?php
namespace Evoweb\StoreFinder\Utility;

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

use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class UpdateUtility
{
    /**
     * @var array
     */
    protected $records = [
        'attributes' => [],
        'categories' => [],
        'locations' => [],
    ];

    /**
     * @var array
     */
    protected $messageArray = [];

    /**
     * @var FieldMapper
     */
    protected $fieldMapper;

    /**
     * Performs the Updates
     * Outputs HTML Content
     *
     * @return string
     */
    public function main(): string
    {
        if ($this->access()) {
            $this->initializeFieldMapper();

            $this->migrateAttributes();
            $this->migrateCategories();
            $this->migrateLocations();
        }

        return $this->generateOutput();
    }

    /**
     * Generates output by using flash messages
     *
     * @return string
     */
    protected function generateOutput(): string
    {
        $output = '<ul class="typo3-messages">';

        foreach ($this->messageArray as $messageItem) {
            /** @var \TYPO3\CMS\Core\Messaging\FlashMessage $flashMessage */
            $flashMessage = GeneralUtility::makeInstance(
                \TYPO3\CMS\Core\Messaging\FlashMessage::class,
                htmlspecialchars($messageItem['message']),
                '',
                \TYPO3\CMS\Core\Messaging\FlashMessage::INFO
            );

            $output .= GeneralUtility::makeInstance(
                \TYPO3\CMS\Core\Messaging\FlashMessageRendererResolver::class
            )->resolve()->render([$flashMessage]);
        }

        $output .= '</ul>';

        return $output;
    }


    /**
     * Migrate attributes
     *
     * @return void
     */
    protected function migrateAttributes()
    {
        $attributes = $this->fetchAttributes();

        while (($row = $attributes->fetch())) {
            $attribute = $this->fieldMapper->mapFieldsPreImport($row, 'attributes');

            $table = 'tx_storefinder_domain_model_attribute';
            $queryBuilder = $this->getQueryBuilderForTable($table);

            if (($record = $this->isAlreadyImported($attribute, $table))) {
                unset($attribute['import_id']);
                $queryBuilder
                    ->getConnection()
                    ->update(
                        $table,
                        $attribute,
                        ['uid' => (int)$record['uid']]
                    );
                $this->records['attributes'][$row['uid']] = $attribute['uid'] = $record['uid'];
            } else {
                $queryBuilder
                    ->insert($table)
                    ->values($attribute)
                    ->execute();
                $this->records['attributes'][$row['uid']] = $attribute['uid'] =
                    $queryBuilder->getConnection()->lastInsertId();
            }

            $this->fieldMapper->migrateFilesToFal($row, $attribute, 'attributes', 'icon');
        }

        $this->messageArray[] = ['message' => count($this->records['attributes']) . ' attributes migrated'];
    }

    /**
     * Migrate categories
     *
     * @return void
     */
    protected function migrateCategories()
    {
        $categories = $this->fetchCategories();

        while (($row = $categories->fetch())) {
            $category = $this->fieldMapper->mapFieldsPreImport($row, 'categories');

            $table = 'sys_category';
            $queryBuilder = $this->getQueryBuilderForTable($table);

            if (($record = $this->isAlreadyImported($category, $table))) {
                unset($category['import_id']);
                $queryBuilder
                    ->getConnection()
                    ->update(
                        $table,
                        $category,
                        ['uid' => (int)$record['uid']]
                    );
                $this->records['categories'][$row['uid']] = $category['uid'] = $record['uid'];
            } else {
                $queryBuilder
                    ->insert($table)
                    ->values($category)
                    ->execute();
                $this->records['categories'][$row['uid']] = $category['uid'] =
                    $queryBuilder->getConnection()->lastInsertId();
            }
        }

        $this->messageArray[] = ['message' => count($this->records['categories']) . ' categories migrated'];
    }

    /**
     * Migrate locations with relations
     *
     * @return void
     */
    protected function migrateLocations()
    {
        $locations = $this->fetchLocations();

        while (($row = $locations->fetch())) {
            $location = $this->fieldMapper->mapFieldsPreImport($row, 'locations');

            $table = 'tx_storefinder_domain_model_location';
            $queryBuilder = $this->getQueryBuilderForTable($table);

            if (($record = $this->isAlreadyImported($location, $table))) {
                unset($location['import_id']);
                $queryBuilder
                    ->getConnection()
                    ->update(
                        $table,
                        $location,
                        ['uid' => (int)$record['uid']]
                    );
                $this->records['locations'][$row['uid']] = $location['uid'] = $record['uid'];
            } else {
                $queryBuilder
                    ->insert($table)
                    ->values($location)
                    ->execute();
                $this->records['locations'][$row['uid']] = $location['uid'] =
                    $queryBuilder->getConnection()->lastInsertId();
            }

            $this->fieldMapper->mapFieldsPostImport($row, $location, 'locations');

            $this->fieldMapper->migrateFilesToFal($row, $location, 'locations', 'media');
            $this->fieldMapper->migrateFilesToFal($row, $location, 'locations', 'imageurl');
            $this->fieldMapper->migrateFilesToFal($row, $location, 'locations', 'icon');
        }

        $queryBuilder = $this->getQueryBuilderForTable('tx_storefinder_domain_model_location');
        $queryBuilder->getConnection()->query('
			update tx_storefinder_domain_model_location AS l
				LEFT JOIN (
					SELECT uid_foreign, COUNT(*) AS count
					FROM sys_category_record_mm
					WHERE tablenames = \'tx_storefinder_domain_model_location\' AND fieldname = \'categories\'
					GROUP BY uid_foreign
				) AS c ON l.uid = c.uid_foreign
			set l.categories = COALESCE(c.count, 0);
		');
        $queryBuilder->getConnection()->query('
			update tx_storefinder_domain_model_location AS l
				LEFT JOIN (
					SELECT uid_local, COUNT(*) AS count
					FROM tx_storefinder_location_attribute_mm
					GROUP BY uid_local
				) AS a ON l.uid = a.uid_local
			set l.attributes = COALESCE(a.count, 0);
		');
        $queryBuilder->getConnection()->query('
			update tx_storefinder_domain_model_location AS l
				LEFT JOIN (
					SELECT uid_local, COUNT(*) AS count
					FROM tx_storefinder_location_location_mm
					GROUP BY uid_local
				) AS a ON l.uid = a.uid_local
			set l.related = COALESCE(a.count, 0);
		');

        $this->messageArray[] = ['message' => count($this->records['locations']) . ' locations migrated'];
    }


    /**
     * Fetch locator attributes
     *
     * @return \Doctrine\DBAL\Driver\Statement
     */
    protected function fetchAttributes(): \Doctrine\DBAL\Driver\Statement
    {
        $queryBuilder = $this->getQueryBuilderForTable('tx_locator_attributes');
        $queryBuilder
            ->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        return $queryBuilder
            ->select('*')
            ->from('tx_locator_attributes')
            ->orderBy('sys_language_uid')
            ->execute();
    }

    /**
     * Fetch locator categories
     *
     * @return \Doctrine\DBAL\Driver\Statement
     */
    protected function fetchCategories(): \Doctrine\DBAL\Driver\Statement
    {
        $queryBuilder = $this->getQueryBuilderForTable('tx_locator_categories');
        $queryBuilder
            ->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        return $queryBuilder
            ->select('*')
            ->from('tx_locator_categories')
            ->orderBy('sys_language_uid')
            ->addOrderBy('parentuid')
            ->execute();
    }

    /**
     * Fetch locator locations
     *
     * @return \Doctrine\DBAL\Driver\Statement
     */
    protected function fetchLocations(): \Doctrine\DBAL\Driver\Statement
    {
        $queryBuilder = $this->getQueryBuilderForTable('tx_locator_locations');
        $queryBuilder
            ->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        return $queryBuilder
            ->select('*')
            ->from('tx_locator_locations')
            ->orderBy('uid')
            ->execute();
    }


    /**
     * Check if a record is already imported
     *
     * @param array $record
     * @param string $table
     *
     * @return array
     */
    protected function isAlreadyImported($record, $table): array
    {
        $queryBuilder = $this->getQueryBuilderForTable($table);
        $queryBuilder
            ->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $row = $queryBuilder
            ->select('uid')
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq('import_id', $record['import_id'])
            )
            ->execute()
            ->fetch();

        return is_array($row) ? $row : [];
    }


    protected function initializeFieldMapper()
    {
        $this->fieldMapper = GeneralUtility::makeInstance(FieldMapper::class);
        $this->fieldMapper->setRecords($this->records);
        $this->fieldMapper->checkPrerequisites();
    }


    /**
     * Check if the update is necessary
     *
     * @return bool True if update should be performed
     */
    public function access(): bool
    {
        $tableNames = $this->getQueryBuilderForTable('pages')->getConnection()->getSchemaManager()->listTableNames();

        $found = false;
        array_walk($tableNames, function ($value) use (&$found) {
            if (strpos($value, 'tx_locator_') !== false) {
                $found = true;
            }
        });

        $countLocations = 0;
        $countAttributes = 0;
        if ($found) {
            $queryBuilder = $this->getQueryBuilderForTable('tx_locator_locations');
            $queryBuilder->getRestrictions()->removeAll();
            $countLocations = $queryBuilder
                ->count('l.uid')
                ->from('tx_locator_locations', 'l')
                ->leftJoin('l', 'tx_storefinder_domain_model_location', 'sl', 'l.uid = sl.import_id')
                ->where(
                    $queryBuilder->expr()->eq('l.deleted', 0),
                    $queryBuilder->expr()->isNull('sl.uid')
                )
                ->execute()
                ->fetchColumn(0);

            $queryBuilder = $this->getQueryBuilderForTable('tx_locator_attributes');
            $queryBuilder->getRestrictions()->removeAll();
            $countAttributes = $queryBuilder
                ->count('a.uid')
                ->from('tx_locator_attributes', 'a')
                ->leftJoin('a', 'tx_storefinder_domain_model_attribute', 'sa', 'a.uid = sa.import_id')
                ->where(
                    $queryBuilder->expr()->eq('a.deleted', 0),
                    $queryBuilder->expr()->isNull('sa.uid')
                )
                ->execute()
                ->fetchColumn(0);
        }

        return $countLocations || $countAttributes;
    }

    protected function getQueryBuilderForTable(string $table): \TYPO3\CMS\Core\Database\Query\QueryBuilder
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Database\ConnectionPool::class
        )->getQueryBuilderForTable($table);
    }
}
