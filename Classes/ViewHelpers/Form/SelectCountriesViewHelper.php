<?php
namespace Evoweb\StoreFinder\ViewHelpers\Form;

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

/**
 * Viewhelper to render a selectbox with values of static info tables countries
 * <code title="Usage">
 * {namespace evoweb=Evoweb\StoreFinder\ViewHelpers}
 * <evoweb:form.SelectStaticCountries name="country" optionLabelField="cnShortDe"/>
 * </code>
 * <code title="Optional label field">
 * {namespace evoweb=Evoweb\StoreFinder\ViewHelpers}
 * <evoweb:form.SelectStaticCountries name="country" optionLabelField="cnShortDe"/>
 * </code>
 */
class SelectCountriesViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\SelectViewHelper
{
    /**
     * Repository that provides the country models
     *
     * @var \Evoweb\StoreFinder\Domain\Repository\CountryRepository
     */
    protected $countryRepository;

    public function injectCountryRepository(
        \Evoweb\StoreFinder\Domain\Repository\CountryRepository $countryRepository
    ) {
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

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('static_info_tables')) {
            $this->countryRepository->setDefaultOrderings([
                'cn_short_local' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING,
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
