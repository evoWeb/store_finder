<?php
declare(strict_types = 1);

// @todo remove after static_info_tables has this configuration
return [
    \SJBR\StaticInfoTables\Domain\Model\Country::class => [
        'tableName' => 'static_countries',
        'properties' => [
            'addressFormat' => [
                'fieldName' => 'cn_address_format'
            ],
            'capitalCity' => [
                'fieldName' => 'cn_capital'
            ],
            'currencyIsoCodeA3' => [
                'fieldName' => 'cn_currency_iso_3'
            ],
            'currencyIsoCodeNumber' => [
                'fieldName' => 'cn_currency_iso_nr'
            ],
            'euMember' => [
                'fieldName' => 'cn_eu_member'
            ],
            'isoCodeA2' => [
                'fieldName' => 'cn_iso_2'
            ],
            'isoCodeA3' => [
                'fieldName' => 'cn_iso_3'
            ],
            'isoCodeNumber' => [
                'fieldName' => 'cn_iso_nr'
            ],
            'officialNameLocal' => [
                'fieldName' => 'cn_official_name_local'
            ],
            'officialNameEn' => [
                'fieldName' => 'cn_official_name_en'
            ],
            'parentTerritoryUnCodeNumber' => [
                'fieldName' => 'cn_parent_tr_iso_nr'
            ],
            'phonePrefix' => [
                'fieldName' => 'cn_phone'
            ],
            'shortNameLocal' => [
                'fieldName' => 'cn_short_local'
            ],
            'shortNameEn' => [
                'fieldName' => 'cn_short_en'
            ],
            'topLevelDomain' => [
                'fieldName' => 'cn_tldomain'
            ],
            'unMember' => [
                'fieldName' => 'cn_uno_member'
            ],
            'zoneFlag' => [
                'fieldName' => 'cn_zone_flag'
            ],
            'countryZones' => [
                'fieldName' => 'cn_country_zones'
            ],
            'deleted' => [
                'fieldName' => 'deleted'
            ],
        ]
    ],
    \SJBR\StaticInfoTables\Domain\Model\CountryZone::class => [
        'tableName' => 'static_country_zones',
        'properties' => [
            'countryIsoCodeA2' => [
                'fieldName' => 'zn_country_iso_2'
            ],
            'countryIsoCodeA3' => [
                'fieldName' => 'zn_country_iso_3'
            ],
            'countryIsoCodeNumber' => [
                'fieldName' => 'zn_country_iso_nr'
            ],
            'isoCode' => [
                'fieldName' => 'zn_code'
            ],
            'localName' => [
                'fieldName' => 'zn_name_local'
            ],
            'nameEn' => [
                'fieldName' => 'zn_name_en'
            ],
            'deleted' => [
                'fieldName' => 'deleted'
            ],
        ]
    ]
];
