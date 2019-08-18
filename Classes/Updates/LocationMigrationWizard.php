<?php
namespace Evoweb\StoreFinder\Updates;

use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

class LocationMigrationWizard implements UpgradeWizardInterface
{
    /**
     * Number of records fetched per database query
     * Used to prevent memory overflows for huge databases
     */
    const RECORDS_PER_QUERY = 1000;

    /**
     * @var string
     */
    protected $title = 'Migrate all location records from EXT:locator to EXT:store_finder tables';

    /**
     * @var array
     */
    protected $records = [
        'attributes' => [],
        'categories' => [],
        'locations' => [],
    ];

    /**
     * @var string
     */
    protected $table = '';

    /**
     * @var array
     */
    protected $messageArray = [];

    /**
     * @var \Evoweb\StoreFinder\Utility\FieldMapper
     */
    protected $fieldMapper;

    /**
     * @var array
     */
    protected $recordOffset = [];

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Constructor
     */
    public function __construct()
    {
        $registry = GeneralUtility::makeInstance(Registry::class);
        $tables = array_keys($this->records);
        foreach ($tables as $table) {
            $wizardClassName = static::class . '/' . $table;
            $done = $registry->get('migrateLocations', $wizardClassName, false);

            if (!$done) {
                $this->table = $table;
                $this->title = 'Migrate all ' . $this->table . ' records.';

                break;
            }
        }
    }

    /**
     * @return string Unique identifier of this updater
     */
    public function getIdentifier(): string
    {
        return 'storeFinderLocationMigration';
    }

    /**
     * @return string Title of this updater
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string Longer description of this updater
     */
    public function getDescription(): string
    {
        return 'This update wizard goes through all files that are referenced in the hw_fewo'
            . ' extension and adds the files to the FAL File Index.<br />'
            . 'It also moves the files from uploads/ to the fileadmin/_migrated/ path.';
    }

    /**
     * Checks if an update is needed
     *
     * @return bool Whether an update is needed (TRUE) or not (FALSE)
     */
    public function updateNecessary(): bool
    {
        return $this->table !== '';
    }

    /**
     * @return string[] All new fields and tables must exist
     */
    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class
        ];
    }

    public function executeUpdate(): bool
    {
        $customMessage = '';
        try {
            $this->initialize();

            $this->migrateAttributes();
            $this->migrateCategories();
            $this->migrateLocationsWithRelations();
        } catch (\Exception $e) {
            $customMessage .= PHP_EOL . $e->getMessage();
        }

        return empty($customMessage);
    }

    protected function initialize()
    {
        $this->registry = GeneralUtility::makeInstance(Registry::class);
        $this->recordOffset = $this->registry->get('migrateLocations', 'recordOffset', []);

        $this->fieldMapper = GeneralUtility::makeInstance(\Evoweb\StoreFinder\Utility\FieldMapper::class);
        $this->fieldMapper->setRecords($this->records);
        $this->fieldMapper->checkPrerequisites();
    }

    /**
     * Marks some wizard as being "seen" so that it not shown again.
     *
     * @param mixed $confValue The configuration is set to this value
     */
    protected function markTablesAsDone($confValue = 1)
    {
        $wizardClassName = static::class . '/' . $this->table;
        $this->registry->set('migrateLocations', $wizardClassName, $confValue);
        $this->registry->remove('migrateLocations', 'recordOffset');
    }

    protected function migrateAttributes()
    {
        if (!isset($this->recordOffset['attributes'])) {
            $this->recordOffset['attributes'] = 0;
        }
        $attributes = $this->fetchAttributes($this->recordOffset['attributes']);

        do {
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
                } else {
                    $queryBuilder
                        ->insert($table)
                        ->values($attribute)
                        ->execute();
                }

                $this->fieldMapper->migrateFilesToFal($row, $attribute, 'attributes', 'icon');
            }
            $this->registry->set('migrateLocations', 'recordOffset', $this->recordOffset);
        } while ($attributes->rowCount() === self::RECORDS_PER_QUERY);

        $this->table = 'attributes';
        $this->markTablesAsDone();

        $this->messageArray[] = ['message' => count($this->records['attributes']) . ' attributes migrated'];
    }

    protected function migrateCategories()
    {
        if (!isset($this->recordOffset['categories'])) {
            $this->recordOffset['categories'] = 0;
        }
        $categories = $this->fetchCategories($this->recordOffset['categories']);

        do {
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
                } else {
                    $queryBuilder
                        ->insert($table)
                        ->values($category)
                        ->execute();
                }
            }
            $this->registry->set('migrateLocations', 'recordOffset', $this->recordOffset);
        } while ($categories->rowCount() === self::RECORDS_PER_QUERY);

        $this->table = 'categories';
        $this->markTablesAsDone();

        $this->messageArray[] = ['message' => count($this->records['categories']) . ' categories migrated'];
    }

    protected function migrateLocationsWithRelations()
    {
        if (!isset($this->recordOffset['locations'])) {
            $this->recordOffset['locations'] = 0;
        }
        $locations = $this->fetchLocations($this->recordOffset['locations']);

        do {
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
                } else {
                    $queryBuilder
                        ->insert($table)
                        ->values($location)
                        ->execute();
                }

                $this->fieldMapper->mapFieldsPostImport($row, $location, 'locations');

                $this->fieldMapper->migrateFilesToFal($row, $location, 'locations', 'media');
                $this->fieldMapper->migrateFilesToFal($row, $location, 'locations', 'imageurl');
                $this->fieldMapper->migrateFilesToFal($row, $location, 'locations', 'icon');
            }
            $this->registry->set('migrateLocations', 'recordOffset', $this->recordOffset);
        } while ($locations->rowCount() === self::RECORDS_PER_QUERY);

        $this->table = 'locations';
        $this->markTablesAsDone();

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

    protected function fetchAttributes(int $offset): \Doctrine\DBAL\Driver\Statement
    {
        $queryBuilder = $this->getQueryBuilderForTable('tx_locator_attributes');
        /** @var DeletedRestriction $deleteRestriction */
        $deleteRestriction = GeneralUtility::makeInstance(DeletedRestriction::class);
        $queryBuilder->getRestrictions()->removeAll()->add($deleteRestriction);
        return $queryBuilder
            ->select('*')
            ->from('tx_locator_attributes')
            ->orderBy('sys_language_uid')
            ->setMaxResults(self::RECORDS_PER_QUERY)
            ->setFirstResult($offset)
            ->execute();
    }

    protected function fetchCategories(int $offset): \Doctrine\DBAL\Driver\Statement
    {
        $queryBuilder = $this->getQueryBuilderForTable('tx_locator_categories');
        /** @var DeletedRestriction $deleteRestriction */
        $deleteRestriction = GeneralUtility::makeInstance(DeletedRestriction::class);
        $queryBuilder->getRestrictions()->removeAll()->add($deleteRestriction);
        return $queryBuilder
            ->select('*')
            ->from('tx_locator_categories')
            ->orderBy('sys_language_uid')
            ->addOrderBy('parentuid')
            ->setMaxResults(self::RECORDS_PER_QUERY)
            ->setFirstResult($offset)
            ->execute();
    }

    protected function fetchLocations(int $offset): \Doctrine\DBAL\Driver\Statement
    {
        $queryBuilder = $this->getQueryBuilderForTable('tx_locator_locations');
        /** @var DeletedRestriction $deleteRestriction */
        $deleteRestriction = GeneralUtility::makeInstance(DeletedRestriction::class);
        $queryBuilder->getRestrictions()->removeAll()->add($deleteRestriction);
        return $queryBuilder
            ->select('*')
            ->from('tx_locator_locations')
            ->orderBy('uid')
            ->setMaxResults(self::RECORDS_PER_QUERY)
            ->setFirstResult($offset)
            ->execute();
    }


    protected function isAlreadyImported(array $record, string $table): array
    {
        $queryBuilder = $this->getQueryBuilderForTable($table);
        /** @var DeletedRestriction $deleteRestriction */
        $deleteRestriction = GeneralUtility::makeInstance(DeletedRestriction::class);
        $queryBuilder->getRestrictions()->removeAll()->add($deleteRestriction);
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


    protected function getQueryBuilderForTable(string $table): \TYPO3\CMS\Core\Database\Query\QueryBuilder
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Database\ConnectionPool::class
        )->getQueryBuilderForTable($table);
    }
}
