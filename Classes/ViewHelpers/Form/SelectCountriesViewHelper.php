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

namespace Evoweb\StoreFinder\ViewHelpers\Form;

use TYPO3\CMS\Core\Country\CountryProvider;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

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
class SelectCountriesViewHelper extends AbstractFormFieldViewHelper
{
    /**
     * @var string
     */
    protected $tagName = 'select';

    public function __construct(protected CountryProvider $countryProvider)
    {
        parent::__construct();
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument(
            'options',
            'array',
            'Associative array with internal IDs as key, and the values are displayed in the select box.
             Can be combined with or replaced by child f:form.select.* nodes.'
        );
        $this->registerArgument(
            'optionsAfterContent',
            'boolean',
            'If true, places auto-generated option tags after those rendered in the tag content. If false,
             automatic options come first.',
            false,
            false
        );
        $this->registerArgument(
            'sortByOptionLabel',
            'boolean',
            'If true, List will be sorted by label.',
            false,
            false
        );
        $this->registerArgument(
            'selectAllByDefault',
            'boolean',
            'If specified options are selected if none was set before.',
            false,
            false
        );
        $this->registerArgument(
            'errorClass',
            'string',
            'CSS class to set if there are errors for this ViewHelper',
            false,
            'f3-form-error'
        );
        $this->registerArgument(
            'prependOptionLabel',
            'string',
            'If specified, will provide an option at first position with the specified label.'
        );
        $this->registerArgument(
            'prependOptionValue',
            'string',
            'If specified, will provide an option at first position with the specified value.'
        );
        $this->registerArgument('multiple', 'boolean', 'If set multiple options may be selected.', false, false);
        $this->registerArgument('required', 'boolean', 'If set no empty value is allowed.', false, false);
        $this->registerArgument('sortByOptionLabel', 'bool', 'If true, List will be sorted by label.', false, true);
        $this->registerArgument(
            'allowedCountries',
            'array',
            'Array with countries allowed to be displayed.',
            false,
            []
        );
        $this->registerArgument(
            'optionValueField',
            'string',
            'If specified, will call the appropriate getter on each object to determine the value.',
            false,
            'alpha2IsoCode'
        );
        $this->registerArgument(
            'optionLabelField',
            'string',
            'If specified, will call the appropriate getter on each object to determine the label.',
            false,
            'localizedNameLabel'
        );
    }

    /**
     * Override the initialize method to load all available countries before rendering
     */
    public function initialize(): void
    {
        parent::initialize();

        if ($this->hasArgument('allowedCountries') && count($this->arguments['allowedCountries'])) {
            $options = [];
            foreach ($this->arguments['allowedCountries'] as $alpha2IsoCode) {
                $options[] = $this->countryProvider->getByAlpha2IsoCode(strtoupper($alpha2IsoCode));
            }
        } else {
            $options = $this->countryProvider->getAll();
        }

        $this->arguments['options'] = $options;
    }

    public function render(): string
    {
        if ($this->arguments['required']) {
            $this->tag->addAttribute('required', 'required');
        }
        $name = $this->getName();
        if ($this->arguments['multiple']) {
            $this->tag->addAttribute('multiple', 'multiple');
            $name .= '[]';
        }
        $this->tag->addAttribute('name', $name);
        // @extensionScannerIgnoreLine
        $options = $this->getOptions();

        $viewHelperVariableContainer = $this->renderingContext->getViewHelperVariableContainer();

        $this->addAdditionalIdentityPropertiesIfNeeded();
        $this->setErrorClassAttribute();
        $content = '';

        // register field name for token generation.
        $this->registerFieldNameForFormTokenGeneration($name);
        // in case it is a multi-select, we need to register the field name
        // as often as there are elements in the box
        if ($this->arguments['multiple']) {
            $content .= $this->renderHiddenFieldForEmptyValue();
            // Register the field name additional times as required by the total number of
            // options. Since we already registered it once above, we start the counter at 1
            // instead of 0.
            $optionsCount = count($options);
            for ($i = 1; $i < $optionsCount; $i++) {
                $this->registerFieldNameForFormTokenGeneration($name);
            }
            // save the parent field name so that any child f:form.select.option
            // tag will know to call registerFieldNameForFormTokenGeneration
            // this is the reason why "self::class" is used instead of static::class (no LSB)
            $viewHelperVariableContainer->addOrUpdate(
                self::class,
                'registerFieldNameForFormTokenGeneration',
                $name
            );
        }

        $viewHelperVariableContainer->addOrUpdate(self::class, 'selectedValue', $this->getSelectedValue());
        $prependContent = $this->renderPrependOptionTag();
        $tagContent = $this->renderOptionTags($options);
        $childContent = $this->renderChildren();
        $viewHelperVariableContainer->remove(self::class, 'selectedValue');
        $viewHelperVariableContainer->remove(self::class, 'registerFieldNameForFormTokenGeneration');
        if (isset($this->arguments['optionsAfterContent']) && $this->arguments['optionsAfterContent']) {
            $tagContent = $childContent . $tagContent;
        } else {
            $tagContent .= $childContent;
        }
        $tagContent = $prependContent . $tagContent;

        $this->tag->forceClosingTag(true);
        $this->tag->setContent($tagContent);
        $content .= $this->tag->render();
        return $content;
    }

    /**
     * Render prepended option tag
     */
    protected function renderPrependOptionTag(): string
    {
        $output = '';
        if ($this->hasArgument('prependOptionLabel')) {
            $value = $this->hasArgument('prependOptionValue') ? $this->arguments['prependOptionValue'] : '';
            $label = $this->arguments['prependOptionLabel'];
            $output .= $this->renderOptionTag((string)$value, (string)$label, false) . LF;
        }
        return $output;
    }

    /**
     * Render the option tags.
     */
    protected function renderOptionTags(array $options): string
    {
        $output = '';
        foreach ($options as $value => $label) {
            $isSelected = $this->isSelected($value);
            $output .= $this->renderOptionTag((string)$value, (string)$label, $isSelected) . LF;
        }
        return $output;
    }

    /**
     * Render the option tags.
     *
     * @return array An associative array of options, key will be the value of the option tag
     */
    protected function getOptions(): array
    {
        if (!is_array($this->arguments['options']) && !$this->arguments['options'] instanceof \Traversable) {
            return [];
        }
        $options = [];
        $optionsArgument = $this->arguments['options'];
        foreach ($optionsArgument as $key => $value) {
            if (is_object($value) || is_array($value)) {
                $key = $this->getOptionKeyFromValue($value);
                $value = $this->getOptionValueFromComplexValue($value);
            }
            if (str_starts_with($value, 'LLL:')) {
                $value = LocalizationUtility::translate($value);
            }
            $options[$key] = $value;
        }
        if ($this->arguments['sortByOptionLabel']) {
            asort($options, SORT_LOCALE_STRING);
        }
        return $options;
    }

    protected function getOptionKeyFromValue($value): string
    {
        $key = '';
        if ($this->hasArgument('optionValueField')) {
            $key = ObjectAccess::getPropertyPath($value, $this->arguments['optionValueField']);
            if (is_object($key)) {
                if (method_exists($key, '__toString')) {
                    $key = (string)$key;
                } else {
                    throw new Exception(
                        'Identifying value for object of class "' . get_debug_type($value)
                        . '" was an object.',
                        1247827428
                    );
                }
            }
        } elseif ($this->persistenceManager->isNewObject($value)) {
            $key = $this->persistenceManager->getIdentifierByObject($value);
        } elseif (is_object($value) && method_exists($value, '__toString')) {
            $key = (string)$value;
        } elseif (is_object($value)) {
            throw new Exception(
                'No identifying value for object of class "' . get_class($value) . '" found.',
                1247826696
            );
        }
        return $key;
    }

    protected function getOptionValueFromComplexValue($value): string
    {
        if ($this->hasArgument('optionLabelField')) {
            $value = ObjectAccess::getPropertyPath($value, $this->arguments['optionLabelField']);
            if (is_object($value)) {
                if (method_exists($value, '__toString')) {
                    $value = (string)$value;
                } else {
                    throw new Exception(
                        'Label value for object of class "'
                        . get_class($value) . '" was an object without a __toString() method.',
                        1247827553
                    );
                }
            }
        } elseif (is_object($value) && method_exists($value, '__toString')) {
            $value = (string)$value;
        } elseif ($this->persistenceManager->isNewObject($value)) {
            $value = $this->persistenceManager->getIdentifierByObject($value);
        }
        return $value;
    }

    /**
     * Render the option tags.
     *
     * @param mixed $value Value to check for
     * @return bool True if the value should be marked as selected.
     */
    protected function isSelected(mixed $value): bool
    {
        $selectedValue = $this->getSelectedValue();
        if ($value === $selectedValue || (string)$value === $selectedValue) {
            return true;
        }
        if ($this->hasArgument('multiple')) {
            if ($this->arguments['selectAllByDefault'] === true) {
                return true;
            }
            if (is_array($selectedValue) && in_array($value, $selectedValue)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieves the selected value(s)
     *
     * @return string|array value string or an array of strings
     */
    protected function getSelectedValue(): string|array
    {
        $this->setRespectSubmittedDataValue(true);
        $value = $this->getValueAttribute();
        if (!is_array($value) && !$value instanceof \Traversable) {
            return $this->getOptionValueScalar($value);
        }
        $selectedValues = [];
        foreach ($value as $selectedValueElement) {
            $selectedValues[] = $this->getOptionValueScalar($selectedValueElement);
        }
        return $selectedValues;
    }

    /**
     * Get the option value for an object
     */
    protected function getOptionValueScalar(mixed $valueElement): string
    {
        if (is_object($valueElement)) {
            if ($this->hasArgument('optionValueField')) {
                return (string)ObjectAccess::getPropertyPath($valueElement, $this->arguments['optionValueField']);
            }
            if ($this->persistenceManager->isNewObject($valueElement)) {
                return (string)$this->persistenceManager->getIdentifierByObject($valueElement);
            }
            return (string)$valueElement;
        }
        return (string)$valueElement;
    }

    /**
     * Render one option tag
     *
     * @param string $value value attribute of the option tag (will be escaped)
     * @param string $label content of the option tag (will be escaped)
     * @param bool $isSelected specifies whether to add selected attribute
     * @return string the rendered option tag
     */
    protected function renderOptionTag(string $value, string $label, bool $isSelected): string
    {
        $output = '<option value="' . htmlspecialchars($value) . '"';
        if ($isSelected) {
            $output .= ' selected="selected"';
        }
        $output .= '>' . htmlspecialchars($label) . '</option>';
        return $output;
    }
}
