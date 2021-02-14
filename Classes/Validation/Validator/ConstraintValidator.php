<?php

declare(strict_types=1);

namespace Evoweb\StoreFinder\Validation\Validator;

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

use Evoweb\StoreFinder\Domain\Model\Constraint;
use TYPO3\CMS\Extbase\Validation\Validator\GenericObjectValidator;
use TYPO3\CMS\Extbase\Validation\Validator\ObjectValidatorInterface;

class ConstraintValidator extends GenericObjectValidator
{
    /**
     * Name of the current field to validate
     *
     * @var string
     */
    protected string $currentPropertyName = '';

    /**
     * Options for the current validation
     *
     * @var array
     */
    protected array $currentValidatorOptions = [];

    /**
     * Model that gets validated currently
     *
     * @var Constraint
     */
    protected Constraint $model;

    /**
     * Checks if the given value is valid according to the property validators.
     *
     * @param Constraint $object The value that should be validated
     */
    protected function isValid($object)
    {
        $this->model = $object;
        foreach ($this->propertyValidators as $propertyName => $validators) {
            $propertyValue = $this->getPropertyValue($object, $propertyName);
            $this->checkProperty($propertyValue, $validators, $propertyName);
        }
    }

    /**
     * Checks if the specified property of the given object is valid, and adds
     * found errors to the $messages object.
     *
     * @param mixed $value The value to be validated
     * @param \Traversable $validators The validators to be called on the value
     * @param string $propertyName Name of the property to check
     */
    protected function checkProperty($value, $validators, $propertyName)
    {
        /** @var \TYPO3\CMS\Extbase\Error\Result $result */
        $result = null;
        foreach ($validators as $validator) {
            if ($validator instanceof SettableInterface) {
                $validator->setModel($this->model);
            }

            if ($validator instanceof ObjectValidatorInterface) {
                $validator->setValidatedInstancesContainer($this->validatedInstancesContainer);
            }
            $currentResult = $validator->validate($value);
            if ($currentResult->hasMessages()) {
                if ($result == null) {
                    $result = $currentResult;
                } else {
                    $result->merge($currentResult);
                }
            }
        }
        if ($result != null) {
            $this->result->forProperty($propertyName)->merge($result);
        }
    }

    /**
     * Checks if validator can validate the object
     *
     * @param Constraint $object
     *
     * @return bool
     */
    public function canValidate($object)
    {
        return $object instanceof Constraint;
    }
}
