<?php
namespace Evoweb\StoreFinder\Utility;

use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FieldMapper
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
     * @var \TYPO3\CMS\Core\Resource\ResourceStorage
     */
    protected $storage;

    /**
     * @var \TYPO3\CMS\Core\Resource\ResourceFactory
     */
    protected $fileFactory;

    /**
     * @var \TYPO3\CMS\Core\Resource\Index\FileIndexRepository
     */
    protected $fileIndexRepository;

    /**
     * @var string
     */
    protected $targetDirectory;

    /**
     * @var array
     */
    protected $records = [];

    public function __construct()
    {
        $this->initializeFalStorage();
    }

    public function setRecords(&$records)
    {
        $this->records &= $records;
    }

    /**
     * Ensures a new folder "fileadmin/content_upload/" is available.
     *
     * @return void
     */
    public function checkPrerequisites()
    {
        if (!$this->storage->hasFolder(self::FILE_MIGRATION_FOLDER)) {
            $this->storage->createFolder(self::FILE_MIGRATION_FOLDER, $this->storage->getRootLevelFolder());
        }
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
     * Map fields pre import
     *
     * @param array $row
     * @param string $table
     *
     * @return array
     */
    public function mapFieldsPreImport($row, $table): array
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
    public function mapFieldsPostImport($source, $destination, $table)
    {
        foreach ($this->mapping[$table] as $fieldFrom => $fieldTo) {
            if (is_array($fieldTo)) {
                switch ($fieldTo[0]) {
                    case 'comma':
                        if ($fieldTo[1] == 'mm') {
                            list(, , , $mmTable, $mmField, $destinationTable, $destinationField) = $fieldTo;
                            $sorting = 0;

                            foreach (GeneralUtility::trimExplode(',', $source[$fieldFrom]) as $fromValue) {
                                $valueA = $destination['uid'];
                                $queryBuilder = $this->getQueryBuilderForTable($destinationTable);
                                $valueB = $queryBuilder
                                    ->select('uid')
                                    ->from($destinationTable)
                                    ->where(
                                        $queryBuilder->expr()->eq(
                                            'import_id',
                                            $queryBuilder->createNamedParameter($fromValue, \PDO::PARAM_INT)
                                        )
                                    )
                                    ->execute()
                                    ->fetchColumn(0);

                                if ($mmField == 'uid_local') {
                                    $fieldA = 'uid_local';
                                    $fieldB = 'uid_foreign';
                                } else {
                                    $fieldA = 'uid_foreign';
                                    $fieldB = 'uid_local';
                                }

                                if (!$valueA || !$valueB) {
                                    continue;
                                }

                                if (!$this->mmRelationExists($mmTable, $valueA, $valueB, $destinationTable)) {
                                    $queryBuilder = $this->getQueryBuilderForTable($mmTable);
                                    $queryBuilder
                                        ->getConnection()
                                        ->insert(
                                            $mmTable,
                                            [
                                                $fieldA => $valueA,
                                                $fieldB => $valueB,
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
                switch (str_replace('finish_', '', $fieldTo[0])) {
                    case 'comma':
                        if ($fieldTo[1] == 'mm') {
                            list(, , , $mmTable, $mmField, $destinationTable, $destinationField) = $fieldTo;
                            $sorting = 0;
                            foreach (GeneralUtility::trimExplode(',', $source[$fieldFrom]) as $fromValue) {
                                $valueA = $destination['uid'];
                                $queryBuilder = $this->getQueryBuilderForTable($destinationTable);
                                $valueB = $queryBuilder
                                    ->select('uid')
                                    ->from($destinationTable)
                                    ->where(
                                        $queryBuilder->expr()->eq(
                                            'import_id',
                                            $queryBuilder->createNamedParameter($fromValue, \PDO::PARAM_INT)
                                        )
                                    )
                                    ->execute()
                                    ->fetchColumn(0);

                                if ($mmField == 'uid_local') {
                                    $fieldA = 'uid_local';
                                    $fieldB = 'uid_foreign';
                                } else {
                                    $fieldA = 'uid_foreign';
                                    $fieldB = 'uid_local';
                                }

                                if (!$valueA || !$valueB) {
                                    continue;
                                }

                                if (!$this->mmRelationExists($mmTable, $valueA, $valueB, $destinationTable)) {
                                    $queryBuilder = $this->getQueryBuilderForTable($mmTable);
                                    $queryBuilder
                                        ->getConnection()
                                        ->insert(
                                            $mmTable,
                                            [
                                                $fieldA => $valueA,
                                                $fieldB => $valueB,
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
     * Processes the actual transformation from CSV to sys_file_references
     *
     * @param array $source
     * @param array $destination
     * @param string $type
     * @param string $field
     *
     * @return void
     */
    public function migrateFilesToFal(array $source, array $destination, string $type, string $field)
    {
        $configuration = $this->fileMapping[$type][$field];
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

    protected function getQueryBuilderForTable(string $table): \TYPO3\CMS\Core\Database\Query\QueryBuilder
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Database\ConnectionPool::class
        )->getQueryBuilderForTable($table);
    }
}
