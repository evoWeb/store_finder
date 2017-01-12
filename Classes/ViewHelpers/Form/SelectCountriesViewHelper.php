<?php
namespace Evoweb\StoreFinder\ViewHelpers\Form;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Sebastian Fischer <typo3@evoweb.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Viewhelper to render a selectbox with values of static info tables countries
 * <code title="Usage">
 * {namespace register=Evoweb\StoreFinder\ViewHelpers}
 * <register:form.SelectStaticCountries name="country"
 *        optionLabelField="cnShortDe"/>
 * </code>
 * <code title="Optional label field">
 * {namespace register=Evoweb\StoreFinder\ViewHelpers}
 * <register:form.SelectStaticCountries name="country"
 *        optionLabelField="cnShortDe"/>
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


    /**
     * @param \Evoweb\StoreFinder\Domain\Repository\CountryRepository $countryRepository
     */
    public function injectCountryRepository(\Evoweb\StoreFinder\Domain\Repository\CountryRepository $countryRepository)
    {
        $this->countryRepository = $countryRepository;
    }


    /**
     * Initialize arguments. Cant be moved to parent because of
     * "private $argumentDefinitions = [];"
     *
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();

        $this->overrideArgument(
            'options',
            'object',
            'Associative array with internal IDs as key, and the values are displayed in the select box',
            false
        );
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
        $this->overrideArgument('sortByOptionLabel', 'boolean', 'If true, List will be sorted by label.', false, false);
        $this->registerArgument(
            'allowedCountries',
            'array',
            'Array with countries allowed to be displayed.',
            false,
            []
        );
    }

    /**
     * Override the initialize method to load all available
     * countries before rendering
     *
     * @return void
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
