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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Validator;
use TYPO3\CMS\Extbase\Validation\Exception\InvalidValidationOptionsException;

/**
 * A required validator to check that a value is set
 *
 * @scope singleton
 */
class EitherValidator extends Validator\AbstractValidator implements Validator\ValidatorInterface
{
    /**
     * @var array
     */
    protected $supportedOptions = array(
        'properties' => array(false, 'Properties to check in either', 'string'),
    );

    /**
     * @var array
     */
    protected $properties = array();

    /**
     * @var \Evoweb\StoreFinder\Domain\Model\Constraint
     */
    protected $model;

    /**
     * @var string
     */
    protected $propertyName;

    /**
     * Constructs the validator and sets validation options
     *
     * @param array $options Options for the validator
     * @throws InvalidValidationOptionsException
     * @api
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);

        if (isset($this->options['properties'])) {
            $this->properties = GeneralUtility::trimExplode(',', $this->options['properties'], true);
        }
    }


    /**
     * @param \Evoweb\StoreFinder\Domain\Model\Constraint $model Constraint to validate
     *
     * @return void
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * @param string $propertyName Property name
     *
     * @return void
     */
    public function setPropertyName($propertyName)
    {
        $this->propertyName = $propertyName;
    }

    /**
     * Override to be able to validate empty values
     *
     * @param mixed $value The value that should be validated
     *
     * @return \TYPO3\CMS\Extbase\Error\Result
     */
    public function validate($value)
    {
        $this->result = new \TYPO3\CMS\Extbase\Error\Result();
        $this->isValid($value);

        return $this->result;
    }

    /**
     * If the given value is empty
     *
     * @param string $value The value
     *
     * @return boolean
     */
    public function isValid($value)
    {
        $result = false;

        if (!empty($value)) {
            $result = true;
        } else {
            $properties = array_diff($this->properties, array($this->propertyName));
            foreach ($properties as $property) {
                $methodName = 'get' . ucfirst($property);
                $value = $this->model->{$methodName}();
                if (!empty($value)) {
                    $result = true;
                }
            }
        }

        if (!$result) {
            $this->addError(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('error_either', 'StoreFinder'),
                1305008423
            );
        }

        return $result;
    }
}
