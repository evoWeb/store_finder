<?php
namespace Evoweb\StoreFinder\Validation\Validator;

/***************************************************************
 * Copyright notice
 *
 * (c) 2014 Sebastian Fischer <typo3@evoweb.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Extbase\Validation\Validator;

/**
 * Class ConstraintValidator
 *
 * @package Evoweb\StoreFinder\Validation\Validator
 */
class ConstraintValidator extends Validator\GenericObjectValidator implements Validator\ValidatorInterface
{
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
    protected $settings;

    /**
     * Validator resolver
     *
     * @var \Evoweb\StoreFinder\Validation\ValidatorResolver
     */
    protected $validatorResolver;


    /**
     * Inject of configuration manager
     *
     * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManager $configurationManager
     *
     * @return void
     */
    public function injectConfigurationManager(
        \TYPO3\CMS\Extbase\Configuration\ConfigurationManager $configurationManager
    ) {
        $this->configurationManager = $configurationManager;
        $this->settings = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS
        );
    }

    /**
     * Inject of validation resolver
     *
     * @param \Evoweb\StoreFinder\Validation\ValidatorResolver $validatorResolver
     */
    public function injectValidatorResolver(\Evoweb\StoreFinder\Validation\ValidatorResolver $validatorResolver)
    {
        $this->validatorResolver = $validatorResolver;
    }


    /**
     * Checks if the given value is valid according to the validator, and returns
     * the Error Messages object which occurred.
     *
     * @param \Evoweb\StoreFinder\Domain\Model\Constraint $value The value that should be validated
     *
     * @return \TYPO3\CMS\Extbase\Error\Result
     */
    public function validate($value)
    {
        $this->result = new \TYPO3\CMS\Extbase\Error\Result();
        if ($this->acceptsEmptyValues === false || $this->isEmpty($value) === false) {
            if (!is_object($value)) {
                $this->addError('Object expected, %1$s given.', 1241099149, [gettype($value)]);
            } elseif ($this->isValidatedAlready($value) === false) {
                $this->addValidatorsBySettings($value);
                $this->isValid($value);
            }
        }

        return $this->result;
    }

    /**
     * Check if validator can validate object
     *
     * @param object $object
     *
     * @return boolean
     */
    public function canValidate($object)
    {
        return is_object($object) && ($object instanceof \Evoweb\StoreFinder\Domain\Model\Constraint);
    }


    /**
     * Get validation rules from settings
     *
     * @param \Evoweb\StoreFinder\Domain\Model\Constraint $value
     */
    protected function addValidatorsBySettings($value)
    {
        $propertyValidators = $this->settings['validation'];
        $notExistMessage = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('error_notexists', 'StoreFinder');

        foreach ($propertyValidators as $propertyName => $validatorsNames) {
            if (!property_exists($value, $propertyName)) {
                $fieldNotExists = str_replace('%1$s', $propertyName, $notExistMessage);
                $this->addError($fieldNotExists, 1301599575);
            } else {
                foreach ($validatorsNames as $validatorsName) {
                    $validator = $this->getValidator($value, $propertyName, $validatorsName);
                    $this->addPropertyValidator($propertyName, $validator);
                }
            }
        }
    }

    /**
     * Parse the rule and instanciate an validator with the name and the options
     *
     * @param \Evoweb\StoreFinder\Domain\Model\Constraint $value
     * @param string $propertyName
     * @param string $rule
     *
     * @throws \InvalidArgumentException
     * @return \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator
     */
    protected function getValidator($value, $propertyName, $rule)
    {
        $currentValidator = $this->parseRule($rule);

        /** @var $validatorObject \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator */
        $validatorObject = $this->validatorResolver->createValidator(
            $currentValidator['validatorName'],
            (array) $currentValidator['validatorOptions']
        );

        if (method_exists($validatorObject, 'setModel')) {
            /** @noinspection PhpUndefinedMethodInspection */
            $validatorObject->setModel($value);
        }

        if (method_exists($validatorObject, 'setPropertyName')) {
            /** @noinspection PhpUndefinedMethodInspection */
            $validatorObject->setPropertyName($propertyName);
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
    protected function parseRule($rule)
    {
        $parsedRules = $this->validatorResolver->getParsedValidatorAnnotation($rule);

        return current($parsedRules['validators']);
    }
}
