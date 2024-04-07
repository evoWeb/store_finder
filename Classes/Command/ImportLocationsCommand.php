<?php

declare(strict_types=1);

namespace Evoweb\StoreFinder\Command;

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

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;

class ImportLocationsCommand extends Command
{
    private array $columnMap = [
        'A' => 'import_id',
        'B' => ['name', 'storeid'],
        'C' => 'address',
        'D' => 'city',
        'E' => 'zipcode',
        'F' => 'country',
        'G' => 'state',
        'H' => 'person',
        'I' => 'url',
        'J' => 'image',
    ];

    private array $attributeMap = [
        'K' => [
            'att1' => 1,
        ],
    ];

    private array $categoryMap = [
        'L' => [
            'cat1' => 1,
        ]
    ];

    private array $countryCache = [];

    private array $stateCache = [];

    public function __construct(
        protected ConnectionPool $connectionPool,
        protected ResourceFactory $resourceFactory
    ) {
        parent::__construct();
    }

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure(): void
    {
        $this
            ->addArgument(
                'fileName',
                InputArgument::REQUIRED,
                'StorageId (most likely 1) and path and filename of excel file
                 that should be imported relatively to the storage (fileadmin)'
            )
            ->addOption(
                'storagePid',
                's',
                InputOption::VALUE_OPTIONAL,
                'Page id to store locations in',
                1
            )
            ->addOption(
                'clearStorageFolder',
                'c',
                InputOption::VALUE_NONE,
                'Page id to store locations in'
            )
            ->addOption(
                'columnMap',
                'o',
                InputOption::VALUE_OPTIONAL,
                'Column map e.G. {A: "import_id", B: {"city", "name"}, C: "zipcode", D: "person", E: "url"}'
            )
            ->addOption(
                'attributeMap',
                'a',
                InputOption::VALUE_OPTIONAL,
                'Attribute map e.G. {K: {"Attribute 1": 1, "Attribute 2": 2}}'
            )
            ->addOption(
                'categoryMap',
                't',
                InputOption::VALUE_OPTIONAL,
                'Category map e.G. {L: {"Category 1": 1, "Category 2": 2}}'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());

        $fileName = $input->getArgument('fileName');
        $storagePid = (int)$input->getOption('storagePid');
        $clearStorageFolder = (bool)$input->getOption('clearStorageFolder');

        if ($input->hasOption('columnMap') && !empty($input->getOption('columnMap'))) {
            $this->columnMap = json_decode($input->getOption('columnMap'), true);
        }
        if ($input->hasOption('attributeMap') && !empty($input->getOption('attributeMap'))) {
            $this->attributeMap = json_decode($input->getOption('attributeMap'), true);
        }
        if ($input->hasOption('categoryMap') && !empty($input->getOption('categoryMap'))) {
            $this->categoryMap = json_decode($input->getOption('categoryMap'), true);
        }

        $file = $this->getFile($fileName);
        $this->processFile($file, $storagePid, $clearStorageFolder, $io);
        return self::SUCCESS;
    }

    protected function getFile(string $fileName): File
    {
        return $this->resourceFactory->getFileObjectFromCombinedIdentifier($fileName);
    }

    protected function processFile(File $file, int $storagePid, bool $clearStorageFolder, SymfonyStyle $io): void
    {
        if ($clearStorageFolder) {
            $this->clearStorageFolder($storagePid);
        }

        $filePath = $file->getForLocalProcessing();
        $reader = IOFactory::createReaderForFile($filePath);
        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getActiveSheet();

        $io->writeln('Import ' . $sheet->getHighestDataRow() . ' possible locations.');
        $progressbar = $io->createProgressBar($sheet->getHighestDataRow());

        $locationCount = 0;
        foreach ($sheet->getRowIterator() as $row) {
            if ($row->isEmpty()) {
                break;
            }
            $progressbar->advance();
            if ($row->getRowIndex() === 1) {
                continue;
            }
            $locationCount++;

            $this->transformAndStoreLocation($row, $storagePid);
        }

        $io->writeln('A total of ' . $locationCount . ' locations were imported');

        unlink($filePath);
    }

    protected function clearStorageFolder(int $storagePid): void
    {
        $tableMm = 'tx_storefinder_location_attribute_mm';
        $fileMm = 'sys_file_reference';
        $tableLocation = 'tx_storefinder_domain_model_location';

        $queryBuilder = $this->getQueryBuilderForTable($tableLocation);
        $expression = $queryBuilder->expr();
        $locationUids = $queryBuilder
            ->select('uid')
            ->from($tableLocation)
            ->where(
                $expression->eq('pid', $storagePid)
            )
            ->executeQuery()
            ->fetchAllAssociative();

        if (count($locationUids)) {
            $queryBuilder = $this->getQueryBuilderForTable($tableMm);
            $expression = $queryBuilder->expr();
            $queryBuilder
                ->delete($tableMm)
                ->where(
                    $expression->in('uid_local', array_column($locationUids, 'uid')),
                    $expression->eq(
                        'tablenames',
                        $queryBuilder->createNamedParameter('tx_storefinder_domain_model_location')
                    ),
                    $expression->eq(
                        'fieldname',
                        $queryBuilder->createNamedParameter('area')
                    )
                )
                ->executeStatement();

            $queryBuilder = $this->getQueryBuilderForTable($fileMm);
            $expression = $queryBuilder->expr();
            $queryBuilder
                ->delete($fileMm)
                ->where(
                    $expression->in('uid_foreign', array_column($locationUids, 'uid')),
                    $expression->eq(
                        'tablenames',
                        $queryBuilder->createNamedParameter('tx_storefinder_domain_model_location')
                    ),
                    $expression->eq(
                        'fieldname',
                        $queryBuilder->createNamedParameter('image')
                    )
                )
                ->executeStatement();

            $connection = $this->getQueryBuilderForTable($tableLocation)->getConnection();
            $connection->delete($tableLocation, ['pid' => $storagePid]);
        }
    }

    protected function transformAndStoreLocation(Row $row, int $storagePid): void
    {
        $attributes = [];
        $categories = [];
        $files = [];
        $location = [
            'pid' => $storagePid,
            'tstamp' => time(),
        ];

        foreach ($row->getCellIterator() as $cell) {
            $sourceColumn = $cell->getColumn();
            $value = (string)$cell->getValue();

            switch (true) {
                case isset($this->attributeMap[$sourceColumn]):
                    if ($this->attributeMap[$sourceColumn][$value]) {
                        $attributes[] = $this->attributeMap[$sourceColumn][$value];
                        $location['area']++;
                    }
                    break;

                case isset($this->categoryMap[$sourceColumn]):
                    if ($this->categoryMap[$sourceColumn][$value]) {
                        $categories[] = $this->categoryMap[$sourceColumn][$value];
                        $location['categories']++;
                    }
                    break;

                case isset($this->columnMap[$sourceColumn]):
                    $targetColumn = $this->columnMap[$sourceColumn];
                    if (is_array($targetColumn)) {
                        foreach ($targetColumn as $targetSubColumn) {
                            $location[$targetSubColumn] = $value;
                        }
                    } elseif ($targetColumn == 'import_id') {
                        $location[$targetColumn] = (int)$value;
                    } elseif ($targetColumn == 'country') {
                        $location[$targetColumn] = $this->fetchCountry($value);
                    } elseif ($targetColumn == 'state') {
                        $location[$targetColumn] = $this->fetchState($value);
                    } elseif (in_array($targetColumn, ['image', 'media', 'icon'])) {
                        if ($fileUid = $this->fetchFile($value)) {
                            $files[$fileUid] = $targetColumn;
                            $location[$targetColumn]++;
                        }
                    } else {
                        $location[$targetColumn] = $value;
                    }
                    break;
            }
        }

        if (!empty($location['city']) || !empty($location['zipcode'])) {
            $location = $this->processLocation($location);
            $this->processAttributes($location['uid'], $attributes);
            $this->processCategories($location['uid'], $categories);
            $this->processFiles($location, $files);
        }
    }

    protected function fetchCountry(string $value): int
    {
        if (!isset($this->countryCache[$value])) {
            $table = 'static_countries';
            $queryBuilder = $this->getQueryBuilderForTable($table);
            $this->countryCache[$value] = (int)$queryBuilder
                ->select('uid')
                ->from($table)
                ->where(
                    $queryBuilder->expr()->eq('cn_iso_3', $queryBuilder->createNamedParameter($value))
                )
                ->executeQuery()
                ->fetchOne();
        }
        return $this->countryCache[$value];
    }

    protected function fetchState(string $value): int
    {
        if (!isset($this->stateCache[$value])) {
            $table = 'static_country_zones';
            $queryBuilder = $this->getQueryBuilderForTable($table);
            $this->stateCache[$value] = (int)$queryBuilder
                ->select('uid')
                ->from($table)
                ->where(
                    $queryBuilder->expr()->eq('zn_code', $queryBuilder->createNamedParameter($value))
                )
                ->executeQuery()
                ->fetchOne();
        }
        return $this->stateCache[$value];
    }

    protected function fetchFile(string $value): string
    {
        return (
            $value === ''
                ? '0'
                : (string)$this->resourceFactory->getFileObjectFromCombinedIdentifier($value)?->getUid()
        );
    }

    protected function processLocation(array $location): array
    {
        $table = 'tx_storefinder_domain_model_location';
        $connection = $this->getQueryBuilderForTable($table)->getConnection();

        $locationUid = $this->getCurrentRecordUid($location, $table);
        if ($locationUid) {
            $location['deleted'] = 0;
            $connection->update($table, $location, ['uid' => $locationUid]);
        } else {
            $location['crdate'] = $location['tstamp'];
            $connection->insert($table, $location);
            $locationUid = (int)$connection->lastInsertId($table);
        }
        $location['uid'] = $locationUid;
        return $location;
    }

    protected function processAttributes(int $locationUid, array $attributes): void
    {
        $table = 'tx_storefinder_location_attribute_mm';
        $tableName = 'tx_storefinder_domain_model_attribute';

        $references = $this->getReferences($table, $tableName, $locationUid, 0);

        foreach ($references as $reference) {
            if (!in_array($reference['uid_foreign'], $attributes)) {
                // remove existing references as it is not current anymore
                $this->removeReference(
                    $table,
                    $tableName,
                    $reference['fieldname'],
                    $locationUid,
                    $reference['uid_foreign']
                );
            } else {
                // existing reference is still current and does not need to be handled anymore
                unset($attributes[$reference['uid_foreign']]);
            }
        }

        foreach ($attributes as $attribute) {
            $this->addReference($table, $tableName, 'area', $locationUid, $attribute);
        }
    }

    protected function processCategories(int $locationUid, array $currentCategories): void
    {
        $table = 'sys_category_record_mm';
        $tableName = 'tx_storefinder_domain_model_location';

        $references = $this->getReferences($table, $tableName, 0, $locationUid);

        foreach ($references as $reference) {
            if (!in_array($reference['uid_local'], $currentCategories)) {
                // remove existing references as it is not current anymore
                $this->removeReference(
                    $table,
                    $tableName,
                    $reference['fieldname'],
                    $reference['uid_local'],
                    $locationUid
                );
            } else {
                // existing reference is still current and does not need to be handled anymore
                unset($currentCategories[$reference['uid_local']]);
            }
        }

        foreach ($currentCategories as $category) {
            $this->addReference($table, $tableName, 'categories', $category, $locationUid);
        }
    }

    protected function processFiles(array $location, array $files): void
    {
        $table = 'sys_file_reference';
        $tableName = 'tx_storefinder_domain_model_location';

        $references = $this->getReferences($table, $tableName, 0, $location['uid']);

        foreach ($references as $reference) {
            if (!isset($files[$reference['uid_local']])) {
                $this->removeReference(
                    $table,
                    $tableName,
                    $reference['fieldname'],
                    $reference['uid_local'],
                    $location['uid']
                );
            } else {
                $data = [
                    'description' => $location['name']
                ];

                $connection = $this->connectionPool->getConnectionForTable($table);
                $connection->update(
                    $table,
                    $data,
                    [
                        'tablenames' => $tableName,
                        'fieldname' => $files[$reference['uid_local']],
                        'uid_local' => $reference['uid_local'],
                        'uid_foreign' => $location['uid']
                    ]
                );
                unset($files[$reference['uid_local']]);
            }
        }

        foreach ($files as $uid => $fieldName) {
            $data = [
                'pid' => $location['pid'],
                'tstamp' => time(),
                'crdate' => time(),
                'table_local' => 'sys_file',
                'description' => $location['name']
            ];

            $this->addReference($table, $tableName, $fieldName, $uid, $location['uid'], $data);
        }
    }

    protected function getCurrentRecordUid(array $location, string $table): int
    {
        $queryBuilder = $this->getQueryBuilderForTable($table);
        $expression = $queryBuilder->expr();
        $result = $queryBuilder
            ->select('uid')
            ->from($table)
            ->where(
                $expression->eq(
                    'pid',
                    $queryBuilder->createNamedParameter($location['pid'], \PDO::PARAM_INT)
                ),
                $expression->eq(
                    'import_id',
                    $queryBuilder->createNamedParameter($location['import_id'])
                )
            )
            ->executeQuery()
            ->fetchOne();
        return (int)$result;
    }

    protected function getReferences(
        string $table,
        string $tableName,
        int $uidLocal,
        int $uidForeign
    ): array {
        $queryBuilder = $this->getQueryBuilderForTable($table);
        $expression = $queryBuilder->expr();
        $queryBuilder
            ->select('*')
            ->from($table)
            ->where(
                $expression->eq(
                    'tablenames',
                    $queryBuilder->createNamedParameter($tableName)
                )
            );

        if ($uidLocal) {
            $queryBuilder->andWhere(
                $expression->eq(
                    'uid_local',
                    $queryBuilder->createNamedParameter($uidLocal, \PDO::PARAM_INT)
                )
            );
        }
        if ($uidForeign) {
            $queryBuilder->andWhere(
                $expression->eq(
                    'uid_foreign',
                    $queryBuilder->createNamedParameter($uidForeign, \PDO::PARAM_INT)
                )
            );
        }
        if ($table === 'sys_file_reference') {
            $queryBuilder->andWhere(
                $expression->eq(
                    'deleted',
                    $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
                )
            );
        }

        return $queryBuilder
            ->executeQuery()
            ->fetchAllAssociative();
    }

    protected function addReference(
        string $table,
        string $tableName,
        string $fieldName,
        int $uidLocal,
        int $uidForeign,
        array $additionalData = []
    ): void {
        $data = [
            'uid_local' => $uidLocal,
            'uid_foreign' => $uidForeign,
            'tablenames' => $tableName,
            'fieldname' => $fieldName,
        ];

        if (!empty($additionalData)) {
            $data = array_merge($additionalData, $data);
        }

        $connection = $this->connectionPool->getConnectionForTable($table);
        $connection->insert($table, $data);
    }

    protected function removeReference(
        string $table,
        string $tableName,
        string $fieldName,
        int $uidLocal,
        int $uidForeign
    ): void {
        $queryBuilder = $this->getQueryBuilderForTable($table);
        $expression = $queryBuilder->expr();
        $queryBuilder
            ->delete($table)
            ->where(
                $expression->eq(
                    'tablenames',
                    $queryBuilder->createNamedParameter($tableName)
                ),
                $expression->eq(
                    'uid_local',
                    $queryBuilder->createNamedParameter($uidLocal, \PDO::PARAM_INT)
                ),
                $expression->eq(
                    'uid_foreign',
                    $queryBuilder->createNamedParameter($uidForeign, \PDO::PARAM_INT)
                )
            );
        if ($fieldName) {
            $queryBuilder->andWhere(
                $expression->eq(
                    'fieldname',
                    $queryBuilder->createNamedParameter($fieldName)
                )
            );
        }
        $queryBuilder->executeStatement();
    }

    protected function getQueryBuilderForTable(string $table): QueryBuilder
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll();
        return $queryBuilder;
    }
}
