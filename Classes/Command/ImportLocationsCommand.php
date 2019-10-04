<?php
declare(strict_types = 1);
namespace Evoweb\StoreFinder\Command;

/**
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ImportLocationsCommand extends \Symfony\Component\Console\Command\Command
{
    private $columnMap = [
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

    private $attributeMap = [
        'K' => [
            'att1' => 1,
        ],
    ];

    private $categoryMap = [
        'L' => [
            'cat1' => 1,
        ]
    ];

    private $countryCache = [];

    private $stateCache = [];

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $this
            ->setDescription(
                'Import locations from excel file into given storage folder (default 1)'
            )
            ->addArgument(
                'fileName',
                InputArgument::REQUIRED,
                'Filename and path of excel file that should be imported'
            )
            ->addArgument(
                'storagePid',
                InputArgument::OPTIONAL,
                'Page id to store locations in',
                1
            )
            ->addArgument(
                'truncateStorage',
                InputArgument::OPTIONAL,
                'Page id to store locations in',
                0
            )
            ->addArgument(
                'columnMap',
                InputArgument::OPTIONAL,
                'Column map {A: "import_id", B: {"city", "name"}, C: "zipcode", D: "person", E: "url"}'
            )
            ->addArgument(
                'attributeMap',
                InputArgument::OPTIONAL,
                'Attribute map {K: {"Attribute 1": 1, "Attribute 2": 2}}'
            )
            ->addArgument(
                'categoryMap',
                InputArgument::OPTIONAL,
                'Category map {L: {"Category 1": 1, "Category 2": 2}}'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());

        $fileName = $input->getArgument('fileName');
        $storagePid = (int) $input->getArgument('storagePid');
        $truncateStorage = (bool) $input->getArgument('truncateStorage');

        if ($input->hasArgument('columnMap') && $input->getArgument('columnMap') !== '') {
            $this->columnMap = json_decode($input->getArgument('columnMap'));
        }
        if ($input->hasArgument('attributeMap') && $input->getArgument('attributeMap') !== '') {
            $this->attributeMap = json_decode($input->getArgument('attributeMap'));
        }
        if ($input->hasArgument('categoryMap') && $input->getArgument('categoryMap') !== '') {
            $this->categoryMap = json_decode($input->getArgument('categoryMap'));
        }

        $file = $this->getFile($fileName);
        $this->processFile($file, $storagePid, $truncateStorage);
    }

    protected function getFile(string $fileName): File
    {
        $resourceFactory = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance();
        return $resourceFactory->getFileObjectFromCombinedIdentifier($fileName);
    }

    protected function processFile(File $file, int $storagePid, bool $truncateStorage)
    {
        if ($truncateStorage) {
            $this->clearTable($storagePid);
        }

        $filePath = $file->getForLocalProcessing();
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
        $spreadsheet = $reader->load($filePath);
        foreach ($spreadsheet->getActiveSheet()->getRowIterator() as $row) {
            if ($row->getRowIndex() === 1) {
                continue;
            }

            $this->transformAndStoreLocation($row, $storagePid);
        }
        unlink($filePath);
    }

    protected function clearTable(int $storagePid)
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
            ->execute()
            ->fetchAll();

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
                        $queryBuilder->createNamedParameter('attributes')
                    )
                )
                ->execute();

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
                ->execute();

            $connection = $this->getQueryBuilderForTable($tableLocation)->getConnection();
            $connection->delete($tableLocation, ['pid' => $storagePid]);
        }
    }

    protected function transformAndStoreLocation(\PhpOffice\PhpSpreadsheet\Worksheet\Row $row, int $storagePid)
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
                        $attributes[] = (string)$this->attributeMap[$sourceColumn][$value];
                    }
                    break;

                case isset($this->categoryMap[$sourceColumn]):
                    if ($this->categoryMap[$sourceColumn][$value]) {
                        $categories[] = (string)$this->categoryMap[$sourceColumn][$value];
                    }
                    break;

                case isset($this->columnMap[$sourceColumn]):
                    $targetColumn = $this->columnMap[$sourceColumn];
                    if (is_array($targetColumn)) {
                        foreach ($targetColumn as $targetSubColumn) {
                            $location[$targetSubColumn] = (string)$value;
                        }
                    } elseif ($targetColumn == 'country') {
                        $location[$targetColumn] = $this->fetchCountry($value);
                    } elseif ($targetColumn == 'state') {
                        $location[$targetColumn] = $this->fetchState($value);
                    } elseif (in_array($targetColumn, ['image', 'media', 'icon'])) {
                        if ($fileUid = $this->fetchFile($value)) {
                            $files[$fileUid] = $targetColumn;
                        }
                    } else {
                        $location[$targetColumn] = (string)$value;
                    }
                    break;
            }
        }

        $locationUid = $this->processLocation($location);
        $location['uid'] = $locationUid;
        $this->processAttributes($locationUid, $attributes);
        $this->processCategories($locationUid, $categories);
        $this->processFiles($location, $files);
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
                ->execute()
                ->fetchColumn(0);
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
                ->execute()
                ->fetchColumn(0);
        }
        return $this->stateCache[$value];
    }

    protected function fetchFile(string $value): string
    {
        if ($value) {
            $resourceFactory = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance();
            $uid = $resourceFactory->getFileObjectFromCombinedIdentifier($value)->getUid();
        } else {
            $uid = 0;
        }
        return (string)$uid;
    }

    protected function processLocation(array $location): string
    {
        $table = 'tx_storefinder_domain_model_location';
        $connection = $this->getQueryBuilderForTable($table)->getConnection();

        $locationUid = $this->getCurrentRecordUid($location, $table);
        if ($locationUid) {
            $connection->update($table, $location, ['uid' => $locationUid]);
        } else {
            $location['crdate'] = $location['tstamp'];
            $connection->insert($table, $location);
            $locationUid = $connection->lastInsertId($table);
        }
        return $locationUid;
    }

    protected function processAttributes(string $locationUid, array $attributes)
    {
        $table = 'tx_storefinder_location_attribute_mm';
        $tableName = 'tx_storefinder_domain_model_attribute';

        $references = $this->getReferences($table, $tableName, $locationUid);

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
            $this->addReference($table, $tableName, 'attributes', $locationUid, $attribute);
        }
    }

    protected function processCategories(string $locationUid, array $currentCategories)
    {
        $table = 'sys_category_record_mm';
        $tableName = 'tx_storefinder_domain_model_location';

        $references = $this->getReferences($table, $tableName, '0', $locationUid);

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

    protected function processFiles(array $location, array $files)
    {
        $table = 'sys_file_reference';
        $references = $this->getReferences(
            $table,
            'tx_storefinder_domain_model_location',
            '',
            '0',
            $location['uid']
        );

        foreach ($references as $reference) {
            if (!isset($files[$reference['uid_local']])) {
                $this->removeReference(
                    $table,
                    'tx_storefinder_domain_model_location',
                    $reference['fieldname'],
                    $reference['uid_local'],
                    $location['uid']
                );
            } else {
                $data = [
                    'description' => $location['name']
                ];

                /** @var \TYPO3\CMS\Core\Database\Connection $connectionPool */
                $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
                $connection->update(
                    $table,
                    $data,
                    [
                        'tablenames' => 'tx_storefinder_domain_model_location',
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
                'uid_local' => $uid,
                'uid_foreign' => $location['uid'],
                'tablenames' => 'tx_storefinder_domain_model_location',
                'fieldname' => $fieldName,
                'description' => $location['name']
            ];

            /** @var \TYPO3\CMS\Core\Database\Connection $connectionPool */
            $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
            $connection->insert($table, $data);
        }
    }

    protected function getCurrentRecordUid(array $location, string $table): string
    {
        $queryBuilder = $this->getQueryBuilderForTable($table);
        $result = (string)$queryBuilder
            ->select('uid')
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq(
                    'pid',
                    $queryBuilder->createNamedParameter($location['pid'], \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'import_id',
                    $queryBuilder->createNamedParameter($location['import_id'])
                )
            )
            ->execute()
            ->fetchColumn(0);
        return $result;
    }

    protected function getReferences(
        string $table,
        string $tableName,
        string $fieldName = '',
        string $uidLocal = '0',
        string $uidForeign = '0'
    ): array {
        $queryBuilder = $this->getQueryBuilderForTable($table);
        $queryBuilder
            ->select('*')
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq(
                    'tablenames',
                    $queryBuilder->createNamedParameter($tableName, \PDO::PARAM_STR)
                )
            );

        if ($fieldName) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq(
                    'fieldname',
                    $queryBuilder->createNamedParameter($fieldName, \PDO::PARAM_STR)
                )
            );
        }
        if ($uidLocal) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq(
                    'uid_local',
                    $queryBuilder->createNamedParameter($uidLocal, \PDO::PARAM_INT)
                )
            );
        }
        if ($uidForeign) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq(
                    'uid_foreign',
                    $queryBuilder->createNamedParameter($uidForeign, \PDO::PARAM_INT)
                )
            );
        }

        return $queryBuilder
            ->execute()
            ->fetchAll();
    }

    protected function addReference(
        string $table,
        string $tableName,
        string $fieldName = '',
        string $uidLocal = '0',
        string $uidForeign = '0'
    ) {
        $data = [
            'uid_local' => $uidLocal,
            'uid_foreign' => $uidForeign,
            'tablenames' => $tableName,
            'fieldname' => $fieldName,
        ];

        /** @var \TYPO3\CMS\Core\Database\Connection $connectionPool */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
        $connection->insert($table, $data);
    }

    protected function removeReference(
        string $table,
        string $tableName,
        string $fieldName = '',
        string $uidLocal = '0',
        string $uidForeign = '0'
    ) {
        $queryBuilder = $this->getQueryBuilderForTable($table);
        $queryBuilder
            ->delete($table)
            ->where(
                $queryBuilder->expr()->eq(
                    'tablenames',
                    $queryBuilder->createNamedParameter($tableName, \PDO::PARAM_STR)
                ),
                $queryBuilder->expr()->eq(
                    'uid_local',
                    $queryBuilder->createNamedParameter($uidLocal, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'uid_foreign',
                    $queryBuilder->createNamedParameter($uidForeign, \PDO::PARAM_INT)
                )
            );
        if ($fieldName) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq(
                    'fieldname',
                    $queryBuilder->createNamedParameter($fieldName, \PDO::PARAM_STR)
                )
            );
        }
        $queryBuilder->execute();
    }

    protected function getQueryBuilderForTable(string $table): \TYPO3\CMS\Core\Database\Query\QueryBuilder
    {
        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Extbase\Object\ObjectManager::class
        );
        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryBuilder = $objectManager
            ->get(ConnectionPool::class)
            ->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll();
        return $queryBuilder;
    }
}
