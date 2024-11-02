<?php

declare(strict_types=1);

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

namespace Evoweb\StoreFinder\Updates;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\ChattyInterface;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

#[UpgradeWizard('sfLocationCountryMigration')]
class LocationCountryMigration implements UpgradeWizardInterface, ChattyInterface
{
    private const TABLE_NAME = 'tx_storefinder_domain_model_location';

    private const VALUE_MAP = [
        1 => 'AD',
        2 => 'AE',
        3 => 'AF',
        4 => 'AG',
        5 => 'AI',
        6 => 'AL',
        7 => 'AM',
        8 => 'AN',
        9 => 'AO',
        10 => 'AQ',
        11 => 'AR',
        12 => 'AS',
        13 => 'AT',
        14 => 'AU',
        15 => 'AW',
        16 => 'AZ',
        17 => 'BA',
        18 => 'BB',
        19 => 'BD',
        20 => 'BE',
        21 => 'BF',
        22 => 'BG',
        23 => 'BH',
        24 => 'BI',
        25 => 'BJ',
        26 => 'BM',
        27 => 'BN',
        28 => 'BO',
        29 => 'BR',
        30 => 'BS',
        31 => 'BT',
        32 => 'BV',
        33 => 'BW',
        34 => 'BY',
        35 => 'BZ',
        36 => 'CA',
        37 => 'CC',
        38 => 'CD',
        39 => 'CF',
        40 => 'CG',
        41 => 'CH',
        42 => 'CI',
        43 => 'CK',
        44 => 'CL',
        45 => 'CM',
        46 => 'CN',
        47 => 'CO',
        48 => 'CR',
        49 => 'CU',
        50 => 'CV',
        51 => 'CX',
        52 => 'CY',
        53 => 'CZ',
        54 => 'DE',
        55 => 'DJ',
        56 => 'DK',
        57 => 'DM',
        58 => 'DO',
        59 => 'DZ',
        60 => 'EC',
        61 => 'EE',
        62 => 'EG',
        63 => 'EH',
        64 => 'ER',
        65 => 'ES',
        66 => 'ET',
        67 => 'FI',
        68 => 'FJ',
        69 => 'FK',
        70 => 'FM',
        71 => 'FO',
        72 => 'FR',
        73 => 'GA',
        74 => 'GB',
        75 => 'GD',
        76 => 'GE',
        77 => 'GF',
        78 => 'GH',
        79 => 'GI',
        80 => 'GL',
        81 => 'GM',
        82 => 'GN',
        83 => 'GP',
        84 => 'GQ',
        85 => 'GR',
        86 => 'GS',
        87 => 'GT',
        88 => 'GU',
        89 => 'GW',
        90 => 'GY',
        91 => 'HK',
        92 => 'HN',
        93 => 'HR',
        94 => 'HT',
        95 => 'HU',
        96 => 'ID',
        97 => 'IE',
        98 => 'IL',
        99 => 'IN',
        100 => 'IO',
        101 => 'IQ',
        102 => 'IR',
        103 => 'IS',
        104 => 'IT',
        105 => 'JM',
        106 => 'JO',
        107 => 'JP',
        108 => 'KE',
        109 => 'KG',
        110 => 'KH',
        111 => 'KI',
        112 => 'KM',
        113 => 'KN',
        114 => 'KP',
        115 => 'KR',
        116 => 'KW',
        117 => 'KY',
        118 => 'KZ',
        119 => 'LA',
        120 => 'LB',
        121 => 'LC',
        122 => 'LI',
        123 => 'LK',
        124 => 'LR',
        125 => 'LS',
        126 => 'LT',
        127 => 'LU',
        128 => 'LV',
        129 => 'LY',
        130 => 'MA',
        131 => 'MC',
        132 => 'MD',
        133 => 'MG',
        134 => 'MH',
        135 => 'MK',
        136 => 'ML',
        137 => 'MM',
        138 => 'MN',
        139 => 'MO',
        140 => 'MP',
        141 => 'MQ',
        142 => 'MR',
        143 => 'MS',
        144 => 'MT',
        145 => 'MU',
        146 => 'MV',
        147 => 'MW',
        148 => 'MX',
        149 => 'MY',
        150 => 'MZ',
        151 => 'NA',
        152 => 'NC',
        153 => 'NE',
        154 => 'NF',
        155 => 'NG',
        156 => 'NI',
        157 => 'NL',
        158 => 'NO',
        159 => 'NP',
        160 => 'NR',
        161 => 'NU',
        162 => 'NZ',
        163 => 'OM',
        164 => 'PA',
        165 => 'PE',
        166 => 'PF',
        167 => 'PG',
        168 => 'PH',
        169 => 'PK',
        170 => 'PL',
        171 => 'PM',
        172 => 'PN',
        173 => 'PR',
        174 => 'PT',
        175 => 'PW',
        176 => 'PY',
        177 => 'QA',
        178 => 'RE',
        179 => 'RO',
        180 => 'RU',
        181 => 'RW',
        182 => 'SA',
        183 => 'SB',
        184 => 'SC',
        185 => 'SD',
        186 => 'SE',
        187 => 'SG',
        188 => 'SH',
        189 => 'SI',
        190 => 'SJ',
        191 => 'SK',
        192 => 'SL',
        193 => 'SM',
        194 => 'SN',
        195 => 'SO',
        196 => 'SR',
        197 => 'ST',
        198 => 'SV',
        199 => 'SY',
        200 => 'SZ',
        201 => 'TC',
        202 => 'TD',
        203 => 'TF',
        204 => 'TG',
        205 => 'TH',
        206 => 'TJ',
        207 => 'TK',
        208 => 'TM',
        209 => 'TN',
        210 => 'TO',
        211 => 'TL',
        212 => 'TR',
        213 => 'TT',
        214 => 'TV',
        215 => 'TW',
        216 => 'TZ',
        217 => 'UA',
        218 => 'UG',
        219 => 'UM',
        220 => 'US',
        221 => 'UY',
        222 => 'UZ',
        223 => 'VA',
        224 => 'VC',
        225 => 'VE',
        226 => 'VG',
        227 => 'VI',
        228 => 'VN',
        229 => 'VU',
        230 => 'WF',
        231 => 'WS',
        232 => 'YE',
        233 => 'YT',
        235 => 'ZA',
        236 => 'ZM',
        237 => 'ZW',
        238 => 'PS',
        239 => 'CS',
        240 => 'AX',
        241 => 'HM',
        242 => 'ME',
        243 => 'RS',
        244 => 'JE',
        245 => 'GG',
        246 => 'IM',
        247 => 'MF',
        248 => 'BL',
        249 => 'BQ',
        250 => 'CW',
        251 => 'SX',
        252 => 'SS',
        253 => 'XK',
    ];

    protected OutputInterface $output;

    public function __construct()
    {
    }

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    public function getTitle(): string
    {
        return 'Migrates location countries from static countries uid to country provider alphaIso2';
    }

    public function getDescription(): string
    {
        return 'Before version 8 store_finder used countries from static_info_tables. As the core now provides
            countries itself, the usage of static_info_tables is dropped and all locations need to be updated
            to retain their selected country.';
    }

    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }

    public function updateNecessary(): bool
    {
        try {
            $necessary = $this->getRecordsToUpdateCount() > 0;
        } catch (Exception) {
            $necessary = 0;
        }
        return $necessary;
    }

    public function executeUpdate(): bool
    {
        $records = $this->getRecordsToUpdate();
        try {
            foreach ($records->fetchAssociative() as $record) {
                $this->updateRecordWithNewCountryValue($record['uid'], self::VALUE_MAP[$record['country']]);
            }
        } catch (Exception $exception) {
            $this->output->write('Querying for locations throws an exception: ' . $exception->getMessage());
        }

        return true;
    }

    /**
     * @throws Exception
     */
    protected function getRecordsToUpdateCount(): int
    {
        $queryBuilder = $this->getPreparedQueryBuilder();
        $expression = $queryBuilder->expr();
        return $queryBuilder
            ->count('uid')
            ->from(self::TABLE_NAME)
            ->where(
                $expression->or(
                    $expression->like('country', $queryBuilder->quote('1%')),
                    $expression->like('country', $queryBuilder->quote('2%')),
                    $expression->like('country', $queryBuilder->quote('3%')),
                    $expression->like('country', $queryBuilder->quote('4%')),
                    $expression->like('country', $queryBuilder->quote('5%')),
                    $expression->like('country', $queryBuilder->quote('6%')),
                    $expression->like('country', $queryBuilder->quote('7%')),
                    $expression->like('country', $queryBuilder->quote('8%')),
                    $expression->like('country', $queryBuilder->quote('9%')),
                )
            )
            ->executeQuery()
            ->fetchOne();
    }

    protected function getRecordsToUpdate(): Result
    {
        $queryBuilder = $this->getPreparedQueryBuilder();
        $expression = $queryBuilder->expr();
        return $queryBuilder
            ->select('uid', 'country')
            ->from(self::TABLE_NAME)
            ->where(
                $expression->or(
                    $expression->like('country', $queryBuilder->quote('1%')),
                    $expression->like('country', $queryBuilder->quote('2%')),
                    $expression->like('country', $queryBuilder->quote('3%')),
                    $expression->like('country', $queryBuilder->quote('4%')),
                    $expression->like('country', $queryBuilder->quote('5%')),
                    $expression->like('country', $queryBuilder->quote('6%')),
                    $expression->like('country', $queryBuilder->quote('7%')),
                    $expression->like('country', $queryBuilder->quote('8%')),
                    $expression->like('country', $queryBuilder->quote('9%')),
                )
            )
            ->executeQuery();
    }

    protected function updateRecordWithNewCountryValue(int $uid, string $countryValue): void
    {
        $queryBuilder = $this->getPreparedQueryBuilder();
        $queryBuilder->update(self::TABLE_NAME)
            ->set('country', $countryValue)
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)
                )
            )
            ->executeStatement();
    }

    protected function getPreparedQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable(self::TABLE_NAME);
        $queryBuilder
            ->getRestrictions()
            ->removeAll();

        return $queryBuilder;
    }

    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
