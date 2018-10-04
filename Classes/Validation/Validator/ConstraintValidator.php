<?php
namespace Evoweb\StoreFinder\Validation\Validator;

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

use TYPO3\CMS\Extbase\Validation\Validator;

class ConstraintValidator extends Validator\GenericObjectValidator implements Validator\ValidatorInterface
{
    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    static protected $instancesCurrentlyUnderValidation;

    /**
     * Object manager
     *
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Configuration manager
     *
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * Settings
     *
     * @var array
     */
    protected $settings = null;

    /**
     * Configuration of the framework
     *
     * @var array
     */
    protected $frameworkConfiguration = [];

    /**
     * Validator resolver
     *
     * @var \Evoweb\StoreFinder\Validation\ValidatorResolver
     */
    protected $validatorResolver;

    /**
     * Name of the current field to validate
     *
     * @var string
     */
    protected $currentPropertyName = '';

    /**
     * Options for the current validation
     *
     * @var array
     */
    protected $currentValidatorOptions = [];

    /**
     * Model that gets validated currently
     *
     * @var \object
     */
    protected $model;

    public function injectObjectManager(
        \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    public function injectConfigurationManager(
        \TYPO3\CMS\Extbase\Configuration\ConfigurationManager $configurationManager
    ) {
        $this->configurationManager = $configurationManager;
        $this->settings = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'StoreFinder'
        );
        $this->frameworkConfiguration = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
        );
    }

    public function injectValidatorResolver(
        \Evoweb\StoreFinder\Validation\ValidatorResolver $validatorResolver
    ) {
        $this->validatorResolver = $validatorResolver;
    }

    /**
     * Validate object
     *
     * @param mixed $object
     *
     * @return \TYPO3\CMS\Extbase\Error\Result
     */
    public function validate($object): \TYPO3\CMS\Extbase\Error\Result
    {
        $this->result = new \TYPO3\CMS\Extbase\Error\Result();
        if (self::$instancesCurrentlyUnderValidation === null) {
            self::$instancesCurrentlyUnderValidation = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        }
        if ($object === null) {
            return $this->result;
        }
        if (!$this->canValidate($object)) {
            $error = new \TYPO3\CMS\Extbase\Error\Error(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('error_notvalidatable', 'StoreFinder'),
                1301599551
            );
            $this->result->addError($error);

            return $this->result;
        }
        if (self::$instancesCurrentlyUnderValidation->contains($object)) {
            return $this->result;
        } else {
            self::$instancesCurrentlyUnderValidation->attach($object);
        }

        $this->model = $object;

        $propertyValidators = $this->getValidationRulesFromSettings();
        foreach ($propertyValidators as $propertyName => $validatorsNames) {
            if (!property_exists($object, $propertyName)) {
                $error = new \TYPO3\CMS\Extbase\Error\Error(
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('error_notexists', 'StoreFinder'),
                    1301599575
                );
                $this->result->addError($error);
            } else {
                $this->currentPropertyName = $propertyName;
                $propertyValue = $this->getPropertyValue($object, $propertyName);
                $this->checkProperty(
                    $propertyValue,
                    (array) $validatorsNames,
                    $this->result->forProperty($propertyName)
                );
            }
        }

        self::$instancesCurrentlyUnderValidation->detach($object);

        return $this->result;
    }

    /**
     * Checks if the specified property of the given object is valid, and adds
     * found errors to the $messages object.
     *
     * @param mixed $value The value to be validated
     * @param array $validatorNames Contains an array with validator names
     * @param string $propertyName Name of the property to check
     */
    protected function checkProperty($value, $validatorNames, $propertyName)
    {
        foreach ($validatorNames as $validatorName) {
            $this->result->merge($this->getValidator($validatorName)->validate($value));
        }
    }

    /**
     * Check if validator can validate object
     *
     * @param \object $object
     *
     * @return bool
     */
    public function canValidate($object): bool
    {
        return ($object instanceof \Evoweb\StoreFinder\Domain\Model\Constraint);
    }

    /**
     * Get validation rules from settings
     *
     * @return array
     */
    protected function getValidationRulesFromSettings(): array
    {
        return (array)$this->settings['validation'];
    }

    /**
     * Parse the rule and instantiate an validator with the name and the options
     *
     * @param string $rule
     *
     * @return Validator\ValidatorInterface
     */
    protected function getValidator(string $rule): Validator\ValidatorInterface
    {
        $currentValidator = $this->parseRule($rule);
        $this->currentValidatorOptions = (array) $currentValidator['validatorOptions'];

        $validatorObject = $this->validatorResolver->createValidator(
            $currentValidator['validatorName'],
            $this->currentValidatorOptions
        );

        if (method_exists($validatorObject, 'setModel')) {
            $validatorObject->setModel($this->model);
        }
        if (method_exists($validatorObject, 'setPropertyName')) {
            $validatorObject->setPropertyName($this->currentPropertyName);
        }

        return $validatorObject;
    }

    /**
     * Parse rule
     *
     * @param string $rule
     *
     * @return array
     */
    protected function parseRule(string $rule): array
    {
        $parsedRules = $this->validatorResolver->getParsedValidatorAnnotation($rule);

        return current($parsedRules['validators']);
    }
}
