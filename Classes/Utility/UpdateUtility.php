<?php
namespace Evoweb\StoreFinder\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class UpdateUtility
 *
 * @package Evoweb\StoreFinder\Utility
 */
class UpdateUtility
{
    /**
     * Folder to migrate files from locator to
     *
     * @var string
     */
    const FILE_MIGRATION_FOLDER = '_store_finder/';

    /**
     * @var \TYPO3\CMS\Core\Database\DatabaseConnection $database
     */
    protected $database;

    /**
     * @var array
     */
    protected $mapping = array(
        'attributes' => array(
            'uid' => 'import_id',
            'pid' => 'pid',
            'tstamp' => 'tstamp',
            'crdate' => 'crdate',
            'cruser_id' => 'cruser_id',
            'sorting' => 'sorting',
            'hidden' => 'hidden',
            'deleted' => 'deleted',
            'sys_language_uid' => 'sys_language_uid',
            'l10n_parent' => 'value:attributes:l18n_parent',
            'l10n_diffsource' => 'l18n_diffsource',
            // icon get migrated at an extra step
            // 'icon' => 'icon',
            'name' => 'name',
        ),

        'categories' => array(
            'uid' => 'import_id',
            'pid' => 'pid',
            'parentuid' => 'value:categories:parent',
            'tstamp' => 'tstamp',
            'crdate' => 'crdate',
            'cruser_id' => 'cruser_id',
            'sorting' => 'sorting',
            'hidden' => 'hidden',
            'deleted' => 'deleted',
            'sys_language_uid' => 'sys_language_uid',
            'l10n_parent' => 'value:categories:l10n_parent',
            'l10n_diffsource' => 'l10n_diffsource',
            // 'fe_group' => '',
            'name' => 'title',
            'description' => 'description',
        ),

        'locations' => array(
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
            'attributes' => 'comma:mm:attributes:tx_storefinder_location_attribute_mm:uid_local:tx_storefinder_domain_model_attribute:attributes',
            'address' => 'address',
            'additionaladdress' => 'additionaladdress',
            'city' => 'city',
            'contactperson' => 'person',
            'state' => 'state',
            'zipcode' => 'zipcode',
            // @todo implement 1:1 references for country
            'country' => 'map:country',
            'products' => 'convert:int:products',
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
            'categoryuid' => 'comma:mm:categories:sys_category_record_mm:uid_foreign:tx_storefinder_domain_model_location:categories',
            'lat' => 'convert:double:latitude',
            'lon' => 'convert:double:longitude',
            'geocode' => '',
            'relatedto' => 'finish_comma:mm:locations:tx_storefinder_location_location_mm:uid_local:tx_storefinder_domain_model_location:related',
        ),
    );

    /**
     * @var array
     */
    protected $fileMapping = array(
        'attributes' => array(
            'icon' => array(
                'sourceField' => 'icon',
                'sourcePath' => 'uploads/tx_locator/icons/',
                'destinationField' => 'icon',
                'destinationTable' => 'tx_storefinder_domain_model_attribute',
            ),
        ),
        'locations' => array(
            'media' => array(
                'sourceField' => 'media',
                'sourcePath' => 'uploads/tx_locator/media/',
                'destinationField' => 'media',
                'destinationTable' => 'tx_storefinder_domain_model_location',
            ),
            'imageurl' => array(
                'sourceField' => 'imageurl',
                'sourcePath' => 'uploads/tx_locator/',
                'destinationField' => 'image',
                'destinationTable' => 'tx_storefinder_domain_model_location',
            ),
            'icon' => array(
                'sourceField' => 'icon',
                'sourcePath' => 'uploads/tx_locator/icons/',
                'destinationField' => 'icon',
                'destinationTable' => 'tx_storefinder_domain_model_location',
            ),
        ),
    );

    /**
     * @var array
     */
    protected $records = array(
        'attributes' => array(),
        'categories' => array(),
        'locations' => array(),
    );

    /**
     * @var array
     */
    protected $messageArray = array();


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
     * Performes the Updates
     * Outputs HTML Content
     *
     * @return string
     */
    public function main()
    {
        $this->database = $GLOBALS['TYPO3_DB'];

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
    protected function renderWarning()
    {
        $action = GeneralUtility::linkThisScript(array(
            'M' => GeneralUtility::_GP('M'),
            'tx_extensionmanager_tools_extensionmanagerextensionmanager' =>
                GeneralUtility::_GP(
                    'tx_extensionmanager_tools_extensionmanagerextensionmanager'
                )
        ));

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
    protected function warningAccepted()
    {
        $updateVars = GeneralUtility::_GP('tx_storefinder_update');

        return (bool) $updateVars['confirm'];
    }

    /**
     * Generates output by using flash messages
     *
     * @return string
     */
    protected function generateOutput()
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

            $severityClass = sprintf('alert %s', $flashMessage->getClass());
            $messageContent = htmlspecialchars($flashMessage->getMessage());
            if ($flashMessage->getTitle() !== '') {
                $messageContent = sprintf('<h4>%s</h4>', htmlspecialchars($flashMessage->getTitle())) . $messageContent;
            }
            $output .= sprintf('<li class="%s">%s</li>', htmlspecialchars($severityClass), $messageContent);
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

        while (($row = $this->database->sql_fetch_assoc($attributes))) {
            $attribute = $this->mapFieldsPreImport($row, 'attributes');

            $table = 'tx_storefinder_domain_model_attribute';
            if (($record = $this->isAlreadyImported($attribute, $table))) {
                unset($attribute['import_id']);
                $this->database->exec_UPDATEquery($table, 'uid = ' . $record['uid'], $attribute);
                $this->records['attributes'][$row['uid']] = $attribute['uid'] = $record['uid'];
            } else {
                $this->database->exec_INSERTquery($table, $attribute);
                $this->records['attributes'][$row['uid']] = $attribute['uid'] = $this->database->sql_insert_id();
            }

            $this->migrateFilesToFal($row, $attribute, $this->fileMapping['attributes']['icon']);
        }

        $this->messageArray[] = array('message' => count($this->records['attributes']) . ' attributes migrated');
    }

    /**
     * Migrate categories
     *
     * @return void
     */
    protected function migrateCategories()
    {
        $categories = $this->fetchCategories();

        while (($row = $this->database->sql_fetch_assoc($categories))) {
            $category = $this->mapFieldsPreImport($row, 'categories');

            $table = 'sys_category';
            if (($record = $this->isAlreadyImported($category, $table))) {
                unset($category['import_id']);
                $this->database->exec_UPDATEquery($table, 'uid = ' . $record['uid'], $category);
                $this->records['categories'][$row['uid']] = $category['uid'] = $record['uid'];
            } else {
                $this->database->exec_INSERTquery($table, $category);
                $this->records['categories'][$row['uid']] = $category['uid'] = $this->database->sql_insert_id();
            }
        }

        $this->messageArray[] = array('message' => count($this->records['categories']) . ' categories migrated');
    }

    /**
     * Migrate locations with relations
     *
     * @return void
     */
    protected function migrateLocations()
    {
        $locations = $this->fetchLocations();

        while (($row = $this->database->sql_fetch_assoc($locations))) {
            $location = $this->mapFieldsPreImport($row, 'locations');

            $table = 'tx_storefinder_domain_model_location';
            if (($record = $this->isAlreadyImported($location, $table))) {
                unset($location['import_id']);
                $this->database->exec_UPDATEquery($table, 'uid = ' . $record['uid'], $location);
                $this->records['locations'][$row['uid']] = $location['uid'] = $record['uid'];
            } else {
                $this->database->exec_INSERTquery($table, $location);
                $this->records['locations'][$row['uid']] = $location['uid'] = $this->database->sql_insert_id();
            }

            $this->mapFieldsPostImport($row, $location, 'locations');

            $this->migrateFilesToFal($row, $location, $this->fileMapping['locations']['media']);
            $this->migrateFilesToFal($row, $location, $this->fileMapping['locations']['imageurl']);
            $this->migrateFilesToFal($row, $location, $this->fileMapping['locations']['icon']);
        }

        $this->database->sql_query('
			update tx_storefinder_domain_model_location AS l
				LEFT JOIN (
					SELECT uid_foreign, COUNT(*) AS count
					FROM sys_category_record_mm
					WHERE tablenames = \'tx_storefinder_domain_model_location\' AND fieldname = \'categories\'
					GROUP BY uid_foreign
				) AS c ON l.uid = c.uid_foreign
			set l.categories = COALESCE(c.count, 0);
		');
        $this->database->sql_query('
			update tx_storefinder_domain_model_location AS l
				LEFT JOIN (
					SELECT uid_local, COUNT(*) AS count
					FROM tx_storefinder_location_attribute_mm
					GROUP BY uid_local
				) AS a ON l.uid = a.uid_local
			set l.attributes = COALESCE(a.count, 0);
		');
        $this->database->sql_query('
			update tx_storefinder_domain_model_location AS l
				LEFT JOIN (
					SELECT uid_local, COUNT(*) AS count
					FROM tx_storefinder_location_location_mm
					GROUP BY uid_local
				) AS a ON l.uid = a.uid_local
			set l.related = COALESCE(a.count, 0);
		');

        $this->messageArray[] = array('message' => count($this->records['locations']) . ' locations migrated');
    }


    /**
     * Fetch locator attributes
     *
     * @return \mysqli_result
     */
    protected function fetchAttributes()
    {
        return $this->database->exec_SELECTquery('*', 'tx_locator_attributes', 'deleted = 0', '', 'sys_language_uid');
    }

    /**
     * Fetch locator categories
     *
     * @return \mysqli_result
     */
    protected function fetchCategories()
    {
        return $this->database->exec_SELECTquery(
            '*',
            'tx_locator_categories',
            'deleted = 0',
            '',
            'sys_language_uid, parentuid'
        );
    }

    /**
     * Fetch locator locations
     *
     * @return \mysqli_result
     */
    protected function fetchLocations()
    {
        return $this->database->exec_SELECTquery('*', 'tx_locator_locations', 'deleted = 0', '', 'uid');
    }


    /**
     * Map fields pre import
     *
     * @param array $row
     * @param string $table
     *
     * @return array
     */
    protected function mapFieldsPreImport($row, $table)
    {
        $result = array();

        foreach ($this->mapping[$table] as $fieldFrom => $fieldTo) {
            if ($fieldTo && strpos($fieldTo, ':') === false) {
                $result[$fieldTo] = is_null($row[$fieldFrom]) ? (string) $row[$fieldFrom] : $row[$fieldFrom];
            } elseif ($fieldTo) {
                $parts = GeneralUtility::trimExplode(':', $fieldTo);

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

                    default:
                }
            }
        }

        return $result;
    }

    protected function mapCountry($value)
    {
        static $countries = null;

        if (is_null($countries)) {
            $countries = $this->getDatabaseConnection()->exec_SELECTgetRows(
                'cn_iso_2, cn_iso_3',
                'static_countries',
                '1',
                '',
                '',
                '',
                'cn_iso_2'
            );
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
            if (strpos($fieldTo, ':') !== false) {
                $parts = GeneralUtility::trimExplode(':', $fieldTo);
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
                                    $this->database->exec_INSERTquery(
                                        $mmTable,
                                        array(
                                            'uid_local' => $uidLocal,
                                            'uid_foreign' => $uidForeign,
                                            'tablenames' => $destinationTable,
                                            'sorting' . ($mmField == 'uid_foreign' ? '_foreign' : '') => $sorting,
                                            'fieldname' => $destinationField,
                                        )
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
            if (strpos($fieldTo, ':') !== false) {
                $parts = GeneralUtility::trimExplode(':', $fieldTo);
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
                                    $this->database->exec_INSERTquery(
                                        $mmTable,
                                        array(
                                            'uid_local' => $uidLocal,
                                            'uid_foreign' => $uidForeign,
                                            'tablenames' => $destinationTable,
                                            'sorting' . ($mmField == 'uid_foreign' ? '_foreign' : '') => $sorting,
                                            'fieldname' => $destinationField,
                                        )
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
     * @param string $tablenames
     *
     * @return bool
     */
    protected function mmRelationExists($mmTable, $uidLocal, $uidForeign, $tablenames)
    {
        return (bool) $this->database->exec_SELECTcountRows(
            '*',
            $mmTable,
            'uid_local = ' . $uidLocal . ' AND uid_foreign = ' . $uidForeign .
            ' AND tablenames = \'' . $tablenames . '\''
        );
    }

    /**
     * Check if a record is already imported
     *
     * @param array $record
     * @param string $table
     *
     * @return bool
     */
    protected function isAlreadyImported($record, $table)
    {
        return $this->database->exec_SELECTgetSingleRow(
            'uid',
            $table,
            'import_id = ' . $record['import_id'] . ' AND deleted = 0'
        );
    }

    /**
     * Count locations
     *
     * @return int
     */
    protected function countStoreFinderLocations()
    {
        return $this->database->exec_SELECTcountRows(
            'uid',
            'tx_storefinder_domain_model_location',
            '1' . \TYPO3\CMS\Backend\Utility\BackendUtility::BEenableFields('tx_storefinder_domain_model_location')
        );
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

                $count = $this->database->exec_SELECTcountRows(
                    '*',
                    'sys_file_reference',
                    'tablenames = ' . $this->database->fullQuoteStr(
                        $configuration['destinationTable'],
                        'sys_file_reference'
                    ) . ' AND fieldname = '
                    . $this->database->fullQuoteStr($configuration['destinationField'], 'sys_file_reference')
                    . ' AND uid_local = ' . $fileObject->getUid() . ' AND uid_foreign = ' . $destination['uid']
                );

                if (!$count) {
                    $dataArray = array(
                        'uid_local' => $fileObject->getUid(),
                        'tablenames' => $configuration['destinationTable'],
                        'uid_foreign' => $destination['uid'],
                        // the sys_file_reference record should always placed on the same page
                        // as the record to link to, see issue #46497
                        'pid' => $source['pid'],
                        'fieldname' => $configuration['destinationField'],
                        'sorting_foreign' => $i,
                        'table_local' => 'sys_file'
                    );
                    $this->database->exec_INSERTquery('sys_file_reference', $dataArray);
                }
            }
            $i++;
        }
    }


    /**
     * echeck if the Ipdate is neassessary
     *
     * @return bool True if update should be perfomed
     */
    public function access()
    {
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $database */
        $database = $GLOBALS['TYPO3_DB'];

        $res = $database->sql_query('show tables like \'tx_locator_%\';');

        $countLocations = $countAttributes = 0;
        if ($database->sql_num_rows($res)) {
            $countLocations = $database->exec_SELECTcountRows(
                'l.uid',
                'tx_locator_locations AS l
                    LEFT JOIN tx_storefinder_domain_model_location AS sl ON l.uid = sl.import_id',
                'l.deleted = 0 AND sl.uid IS NULL'
            );
            $countAttributes = $database->exec_SELECTcountRows(
                'a.uid',
                'tx_locator_attributes AS a
                    LEFT JOIN tx_storefinder_domain_model_attribute AS sa ON a.uid = sa.import_id',
                'a.deleted = 0 AND sa.uid IS NULL'
            );
        }

        $result = false;
        if ($countLocations || $countAttributes) {
            $result = true;
        }

        return $result;
    }

    /**
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
