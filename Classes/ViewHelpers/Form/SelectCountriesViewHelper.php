<?php

declare(strict_types=1);

namespace Evoweb\StoreFinder\ViewHelpers\Form;

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

use Evoweb\StoreFinder\Domain\Repository\CountryRepository;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Fluid\ViewHelpers\Form\SelectViewHelper;

/**
 * Viewhelper to render a select element with values of static info tables countries
 * <code title="Usage">
 * {namespace evoweb=Evoweb\StoreFinder\ViewHelpers}
 * <evoweb:form.SelectStaticCountries name="country" optionLabelField="cnShortDe"/>
 * </code>
 * <code title="Optional label field">
 * {namespace evoweb=Evoweb\StoreFinder\ViewHelpers}
 * <evoweb:form.SelectStaticCountries name="country" optionLabelField="cnShortDe"/>
 * </code>
 */
class SelectCountriesViewHelper extends SelectViewHelper
{
    protected ?CountryRepository $countryRepository;

    public function injectCountryRepository(CountryRepository $countryRepository)
    {
        $this->countryRepository = $countryRepository;
    }

    public function initializeArguments()
    {
        parent::initializeArguments();

        $this->overrideArgument(
            'optionValueField',
            'string',
            'If specified, will call the appropriate getter on each object to determine the value.',
            false,
            'isoCodeA2'
        );
        $this->overrideArgument(
            'optionLabelField',
            'string',
            'If specified, will call the appropriate getter on each object to determine the label.',
            false,
            'shortNameLocal'
        );
        $this->registerArgument(
            'allowedCountries',
            'array',
            'Array with countries allowed to be displayed.',
            false,
            []
        );
    }

    /**
     * Override the initialize method to load all available countries before rendering
     */
    public function initialize()
    {
        parent::initialize();

        if (ExtensionManagementUtility::isLoaded('static_info_tables')) {
            $this->countryRepository->setDefaultOrderings([
                'cn_short_local' => QueryInterface::ORDER_ASCENDING,
            ]);

            if ($this->hasArgument('allowedCountries') && count($this->arguments['allowedCountries'])) {
                $result = $this->countryRepository->findByIsoCodeA2($this->arguments['allowedCountries']);
            } else {
                $result = $this->countryRepository->findAll();
            }

            if (!empty($this->arguments['allowedCountries'])) {
                $orderedResults = [];
                foreach ($this->arguments['allowedCountries'] as $countryKey) {
                    foreach ($result as $country) {
                        if ($country->getIsoCodeA2() == $countryKey) {
                            $orderedResults[] = $country;
                        }
                    }
                }
                $result = $orderedResults;
            }

            $this->arguments['options'] = [];
            foreach ($result as $country) {
                $this->arguments['options'][] = $country;
            }
        }
    }
}
