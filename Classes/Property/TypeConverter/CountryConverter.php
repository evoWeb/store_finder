<?php

namespace Evoweb\StoreFinder\Property\TypeConverter;

use TYPO3\CMS\Core\Country\Country;
use TYPO3\CMS\Core\Country\CountryProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter;

class CountryConverter extends AbstractTypeConverter
{
    /**
     * @param array<string, mixed> $convertedChildProperties
     */
    public function convertFrom(
        $source,
        string $targetType,
        array $convertedChildProperties = [],
        ?PropertyMappingConfigurationInterface $configuration = null
    ): ?Country {
        if ($source === '' || !is_string($source)) {
            return null;
        }
        /** @var CountryProvider $countryProvider */
        $countryProvider = GeneralUtility::makeInstance(CountryProvider::class);
        return $countryProvider->getByAlpha2IsoCode($source);
    }
}
