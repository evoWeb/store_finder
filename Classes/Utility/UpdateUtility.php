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
     * Folder to migrate files from locator to
     *
     * @var string
     */
    const FILE_MIGRATION_FOLDER = '_store_finder/';

    /**
     * @var array
     */
    protected $mapping = [
        'attributes' => [
            'uid' => 'import_id',
            'pid' => 'pid',
            'tstamp' => 'tstamp',
            'crdate' => 'crdate',
            'cruser_id' => 'cruser_id',
            'sorting' => 'sorting',
            'hidden' => 'hidden',
            'deleted' => 'deleted',
            'sys_language_uid' => 'sys_language_uid',
            'l10n_parent' => [
                'value',
                'attributes',
                'l18n_parent'
            ],
            'l10n_diffsource' => 'l18n_diffsource',
            // icon get migrated at an extra step
            // 'icon' => 'icon',
            'name' => 'name',
        ],

        'categories' => [
            'uid' => 'import_id',
            'pid' => 'pid',
            'parentuid' => [
                'value',
                'categories',
                'parent'
            ],
            'tstamp' => 'tstamp',
            'crdate' => 'crdate',
            'cruser_id' => 'cruser_id',
            'sorting' => 'sorting',
            'hidden' => 'hidden',
            'deleted' => 'deleted',
            'sys_language_uid' => 'sys_language_uid',
            'l10n_parent' => [
                'value',
                'categories',
                'l10n_parent'
            ],
            'l10n_diffsource' => 'l10n_diffsource',
            // 'fe_group' => '',
            'name' => 'title',
            'description' => 'description',
        ],

        'locations' => [
            'uid' => 'import_id',
            'pid' => 'pid',
            'tstamp' => 'tstamp',
            'crdate' => 'crdate',
            'cruser_id' => 'cruser_id',
            'sorting' => 'sorting',
            'deleted' => 'deleted',
            'hidden' => 'hidden',
            'endtime' => 'endtime',
            'fe_group' => 'fe_group',
            'storename' => 'name',
            'storeid' => 'storeid',
            'attributes' => [
                'comma',
                'mm',
                'attributes',
                'tx_storefinder_location_attribute_mm',
                'uid_local',
                'tx_storefinder_domain_model_attribute',
                'attributes',
            ],
            'address' => 'address',
            'additionaladdress' => 'additionaladdress',
            'city' => 'city',
            'contactperson' => 'person',
            'state' => 'state',
            'zipcode' => 'zipcode',
            // @todo implement 1:1 references for country
            'country' => [
                'map',
                'country'
            ],
            'products' => [
                'convert',
                'int',
                'products'
            ],
            'email' => 'email',
            'phone' => 'phone',
            'mobile' => 'mobile',
            'fax' => 'fax',
            'hours' => 'hours',
            'url' => 'url',
            'notes' => 'notes',
            // media get migrated at an extra step
            // 'media' => 'media',
            // imageurl get migrated at an extra step
            // 'imageurl' => 'image',
            // icon get migrated at an extra step
            // 'icon' => 'icon',
            'content' => 'content',
            'use_coordinate' => '',
            'categoryuid' => [
                'comma',
                'mm',
                'categories',
                'sys_category_record_mm',
                'uid_foreign',
                'tx_storefinder_domain_model_location',
                'categories'
            ],
            'lat' => [
                'convert',
                'double',
                'latitude'
            ],
            'lon' => [
                'convert',
                'double',
                'longitude'
            ],
            'geocode' => '',
            'relatedto' => [
                'finish_comma',
                'mm',
                'locations',
                'tx_storefinder_location_location_mm',
                'uid_local',
                'tx_storefinder_domain_model_location',
                'related'
            ],
        ],
    ];

    /**
     * @var array
     */
    protected $fileMapping = [
        'attributes' => [
            'icon' => [
                'sourceField' => 'icon',
                'sourcePath' => 'uploads/tx_locator/icons/',
                'destinationField' => 'icon',
                'destinationTable' => 'tx_storefinder_domain_model_attribute',
            ],
        ],
        'locations' => [
            'media' => [
                'sourceField' => 'media',
                'sourcePath' => 'uploads/tx_locator/media/',
                'destinationField' => 'media',
                'destinationTable' => 'tx_storefinder_domain_model_location',
            ],
            'imageurl' => [
                'sourceField' => 'imageurl',
                'sourcePath' => 'uploads/tx_locator/',
                'destinationField' => 'image',
                'destinationTable' => 'tx_storefinder_domain_model_location',
            ],
            'icon' => [
                'sourceField' => 'icon',
                'sourcePath' => 'uploads/tx_locator/icons/',
                'destinationField' => 'icon',
                'destinationTable' => 'tx_storefinder_domain_model_location',
            ],
        ],
    ];

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
     * @var string
     */
    protected $targetDirectory;

    /**
     * @var \TYPO3\CMS\Core\Resource\ResourceFactory
     */
    protected $fileFactory;

    /**
     * @var \TYPO3\CMS\Core\Resource\Index\FileIndexRepository
     */
    protected $fileIndexRepository;

    /**
     * @var \TYPO3\CMS\Core\Resource\ResourceStorage
     */
    protected $storage;


    /**
     * Performs the Updates
     * Outputs HTML Content
     *
     * @return string
     */
    public function main(): string
    {
        $content = '';

        if ($this->access()) {
            if ($this->warningAccepted()) {
                $this->initializeFalStorage();
                $this->checkPrerequisites();

                $this->migrateAttributes();
                $this->migrateCategories();
                $this->migrateLocations();
            } else {
                $content = $this->renderWarning();
            }
        }

        return $content ?: $this->generateOutput();
    }


    /**
     * Render warning
     *
     * @return string
     */
    protected function renderWarning(): string
    {
        $action = GeneralUtility::linkThisScript([
            'M' => GeneralUtility::_GP('M'),
            'tx_extensionmanager_tools_extensionmanagerextensionmanager' =>
                GeneralUtility::_GP('tx_extensionmanager_tools_extensionmanagerextensionmanager')
        ]);

        $content = sprintf('</br>Do you want to start the migration?</br>
            <form action="%1$s" method="POST">
                <button name="tx_storefinder_update[confirm]" value="1">Start migration</button>
            </form>', $action);

        return $content;
    }

    /**
     * Check if warning was confirmed
     *
     * @return bool
     */
    protected function warningAccepted(): bool
    {
        $updateVars = GeneralUtility::_GP('tx_storefinder_update');

        return (bool) $updateVars['confirm'];
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
            $attribute = $this->mapFieldsPreImport($row, 'attributes');

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

            $this->migrateFilesToFal($row, $attribute, $this->fileMapping['attributes']['icon']);
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
            $category = $this->mapFieldsPreImport($row, 'categories');

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
            $location = $this->mapFieldsPreImport($row, 'locations');

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

            $this->mapFieldsPostImport($row, $location, 'locations');

            $this->migrateFilesToFal($row, $location, $this->fileMapping['locations']['media']);
            $this->migrateFilesToFal($row, $location, $this->fileMapping['locations']['imageurl']);
            $this->migrateFilesToFal($row, $location, $this->fileMapping['locations']['icon']);
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
     * Map fields pre import
     *
     * @param array $row
     * @param string $table
     *
     * @return array
     */
    protected function mapFieldsPreImport($row, $table): array
    {
        $result = [];

        foreach ($this->mapping[$table] as $fieldFrom => $fieldTo) {
            if (!is_array($fieldTo)) {
                $result[$fieldTo] = is_null($row[$fieldFrom]) ? (string) $row[$fieldFrom] : $row[$fieldFrom];
            } elseif ($fieldTo) {
                $parts = $fieldTo;
                switch ($parts[0]) {
                    case 'value':
                        $result[$parts[2]] = (string) $this->records[$parts[1]][$row[$fieldFrom]];
                        break;

                    case 'map':
                        if ($parts[1] == 'country') {
                            $result[$parts[1]] = $this->mapCountry($row[$fieldFrom]);
                        }
                        break;

                    case 'convert':
                        if ($parts[1] == 'int') {
                            $result[$parts[2]] = intval($row[$fieldFrom]);
                        }
                        if ($parts[1] == 'double' || $parts[1] == 'float') {
                            $result[$parts[2]] = floatval($row[$fieldFrom]);
                        }
                        break;
                }
            }
        }

        return $result;
    }

    protected function mapCountry($value): string
    {
        static $countries = null;

        if (is_null($countries)) {
            $queryBuilder = $this->getQueryBuilderForTable('static_countries');
            $queryBuilder
                ->getRestrictions()
                ->removeAll()
                ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
            $rows = $queryBuilder
                ->select('cn_iso_2', 'cn_iso_3')
                ->from('static_countries')
                ->execute();
            while ($row = $rows->fetch()) {
                $countries[$row['cn_iso_2']] = $row;
            }
        }

        return (string)$countries[$value]['cn_iso_3'];
    }

    /**
     * Map fields post import
     *
     * @param array $source
     * @param array $destination
     * @param string $table
     *
     * @return void
     */
    protected function mapFieldsPostImport($source, $destination, $table)
    {
        foreach ($this->mapping[$table] as $fieldFrom => $fieldTo) {
            if (is_array($fieldTo)) {
                $parts = $fieldTo;
                switch ($parts[0]) {
                    case 'comma':
                        if ($parts[1] == 'mm') {
                            list(, , $sourceModel, $mmTable, $mmField, $destinationTable, $destinationField) = $parts;
                            $sorting = 0;

                            foreach (GeneralUtility::trimExplode(',', $source[$fieldFrom]) as $fromValue) {
                                if ($mmField == 'uid_local') {
                                    $uidForeign = $this->records[$sourceModel][$fromValue];
                                    $uidLocal = $destination['uid'];
                                } else {
                                    $uidLocal = $this->records[$sourceModel][$fromValue];
                                    $uidForeign = $destination['uid'];
                                }

                                if (!$uidLocal || !$uidForeign) {
                                    continue;
                                }

                                if (!$this->mmRelationExists($mmTable, $uidLocal, $uidForeign, $destinationTable)) {
                                    $queryBuilder = $this->getQueryBuilderForTable($mmTable);
                                    $queryBuilder
                                        ->getConnection()
                                        ->insert(
                                            $mmTable,
                                            [
                                                'uid_local' => $uidLocal,
                                                'uid_foreign' => $uidForeign,
                                                'tablenames' => $destinationTable,
                                                'sorting' . ($mmField == 'uid_foreign' ? '_foreign' : '') => $sorting,
                                                'fieldname' => $destinationField,
                                            ]
                                        );
                                }

                                $sorting++;
                            }
                        }
                        break;

                    default:
                }
            }
        }
    }

    /**
     * Map fields after all records are imported
     *
     * @param array $source
     * @param array $destination
     * @param string $table
     *
     * @return void
     */
    protected function mapFieldsFinish($source, $destination, $table)
    {
        foreach ($this->mapping[$table] as $fieldFrom => $fieldTo) {
            if (is_array($fieldTo)) {
                $parts = $fieldTo;
                switch (str_replace('finish_', '', $parts[0])) {
                    case 'comma':
                        if ($parts[1] == 'mm') {
                            list(, , $sourceModel, $mmTable, $mmField, $destinationTable, $destinationField) = $parts;
                            $sorting = 0;
                            foreach (GeneralUtility::trimExplode(',', $source[$fieldFrom]) as $fromValue) {
                                if ($mmField == 'uid_local') {
                                    $uidForeign = $this->records[$sourceModel][$fromValue];
                                    $uidLocal = $destination['uid'];
                                } else {
                                    $uidLocal = $this->records[$sourceModel][$fromValue];
                                    $uidForeign = $destination['uid'];
                                }

                                if (!$uidLocal || !$uidForeign) {
                                    continue;
                                }

                                if (!$this->mmRelationExists($mmTable, $uidLocal, $uidForeign, $destinationTable)) {
                                    $queryBuilder = $this->getQueryBuilderForTable($mmTable);
                                    $queryBuilder
                                        ->getConnection()
                                        ->insert(
                                            $mmTable,
                                            [
                                                'uid_local' => $uidLocal,
                                                'uid_foreign' => $uidForeign,
                                                'tablenames' => $destinationTable,
                                                'sorting' . ($mmField == 'uid_foreign' ? '_foreign' : '') => $sorting,
                                                'fieldname' => $destinationField,
                                            ]
                                        );
                                }

                                $sorting++;
                            }
                        }
                        break;

                    default:
                }
            }
        }
    }

    /**
     * Checks if a mm relation exists
     *
     * @param string $mmTable
     * @param int $uidLocal
     * @param int $uidForeign
     * @param string $tableNames
     *
     * @return bool
     */
    protected function mmRelationExists($mmTable, $uidLocal, $uidForeign, $tableNames): bool
    {
        $queryBuilder = $this->getQueryBuilderForTable($mmTable);
        $count = $queryBuilder
            ->count('*')
            ->from($mmTable)
            ->where(
                $queryBuilder->expr()->eq('uid_local', $uidLocal),
                $queryBuilder->expr()->eq('uid_foreign', $uidForeign),
                $queryBuilder->expr()->eq('tablenames', $tableNames)
            )
            ->execute()
            ->fetchColumn(0);
        return $count > 0;
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


    /**
     * Prepare FAL storage for migration
     *
     * @throws \RuntimeException
     * @return void
     */
    protected function initializeFalStorage()
    {
        if (!$this->storage) {
            $fileadminDirectory = !empty($GLOBALS['TYPO3_CONF_VARS']['BE']['fileadminDir']) ?
                rtrim($GLOBALS['TYPO3_CONF_VARS']['BE']['fileadminDir'], '/') . '/' :
                'fileadmin/';

            /** @var $storageRepository \TYPO3\CMS\Core\Resource\StorageRepository */
            $storageRepository = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\StorageRepository::class);
            $storages = $storageRepository->findAll();

            foreach ($storages as $storage) {
                $storageRecord = $storage->getStorageRecord();
                $configuration = $storage->getConfiguration();
                $isLocalDriver = $storageRecord['driver'] === 'Local';
                $isOnFileadmin = !empty($configuration['basePath'])
                    && GeneralUtility::isFirstPartOfStr($configuration['basePath'], $fileadminDirectory);
                if ($isLocalDriver && $isOnFileadmin) {
                    $this->storage = $storage;
                    break;
                }
            }

            if (!isset($this->storage)) {
                throw new \RuntimeException(
                    'Local default storage could not be initialized - might be due to missing sys_file* tables.'
                );
            }

            $this->fileFactory = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\ResourceFactory::class);
            $this->fileIndexRepository = GeneralUtility::makeInstance(
                \TYPO3\CMS\Core\Resource\Index\FileIndexRepository::class
            );
            $this->targetDirectory = PATH_site . $fileadminDirectory . self::FILE_MIGRATION_FOLDER;
        }
    }

    /**
     * Ensures a new folder "fileadmin/content_upload/" is available.
     *
     * @return void
     */
    protected function checkPrerequisites()
    {
        if (!$this->storage->hasFolder(self::FILE_MIGRATION_FOLDER)) {
            $this->storage->createFolder(self::FILE_MIGRATION_FOLDER, $this->storage->getRootLevelFolder());
        }
    }

    /**
     * Processes the actual transformation from CSV to sys_file_references
     *
     * @param array $source
     * @param array $destination
     * @param array $configuration
     *
     * @return void
     */
    protected function migrateFilesToFal(array $source, array $destination, array $configuration)
    {
        $path = PATH_site . $configuration['sourcePath'];
        $files = GeneralUtility::trimExplode(',', $source[$configuration['sourceField']], true);

        $i = 1;
        foreach ($files as $file) {
            if (file_exists($path . $file)) {
                GeneralUtility::upload_copy_move($path . $file, $this->targetDirectory . $file);
                /** @var \TYPO3\CMS\Core\Resource\File $fileObject */
                $fileObject = $this->storage->getFile(self::FILE_MIGRATION_FOLDER . $file);
                $this->fileIndexRepository->add($fileObject);

                $queryBuilder = $this->getQueryBuilderForTable('sys_file_reference');
                $queryBuilder
                    ->getRestrictions()
                    ->removeAll();
                $count = $queryBuilder
                    ->count('*')
                    ->from('sys_file_reference')
                    ->where(
                        $queryBuilder->expr()->eq(
                            'tablenames',
                            $queryBuilder->createNamedParameter($configuration['destinationTable'], \PDO::PARAM_STR)
                        ),
                        $queryBuilder->expr()->eq(
                            'fieldname',
                            $queryBuilder->createNamedParameter($configuration['destinationField'], \PDO::PARAM_STR)
                        ),
                        $queryBuilder->expr()->eq(
                            'uid_local',
                            $queryBuilder->createNamedParameter($fileObject->getUid(), \PDO::PARAM_INT)
                        ),
                        $queryBuilder->expr()->eq(
                            'uid_foreign',
                            $queryBuilder->createNamedParameter($destination['uid'], \PDO::PARAM_INT)
                        )
                    )
                    ->execute()
                    ->fetchColumn();

                if (!$count) {
                    $queryBuilder = $this->getQueryBuilderForTable('sys_file_reference');
                    $queryBuilder
                        ->getConnection()
                        ->insert(
                            'sys_file_reference',
                            [
                                'uid_local' => $fileObject->getUid(),
                                'tablenames' => $configuration['destinationTable'],
                                'uid_foreign' => $destination['uid'],
                                // the sys_file_reference record should always placed on the same page
                                // as the record to link to, see issue #46497
                                'pid' => $source['pid'],
                                'fieldname' => $configuration['destinationField'],
                                'sorting_foreign' => $i,
                                'table_local' => 'sys_file'
                            ]
                        );
                }
            }
            $i++;
        }
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
