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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Validator;

/**
 * A either validator to check that a value is set
 */
class EitherValidator extends Validator\AbstractValidator implements Validator\ValidatorInterface
{
    /**
     * @var array
     */
    protected $supportedOptions = [
        'properties' => [false, 'Properties to check in either', 'string'],
    ];

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * @var \Evoweb\StoreFinder\Domain\Model\Constraint
     */
    protected $model;

    /**
     * @var string
     */
    protected $propertyName;

    /**
     * @var bool
     */
    protected $acceptsEmptyValues = false;

    /**
     * Constructs the validator and sets validation options
     *
     * @param array $options Options for the validator
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        if (isset($this->options['properties'])) {
            $this->properties = GeneralUtility::trimExplode(',', $this->options['properties'], true);
        }
    }

    public function setModel(\Evoweb\StoreFinder\Domain\Model\Constraint $model)
    {
        $this->model = $model;
    }

    /**
     * @param string $propertyName Property name
     */
    public function setPropertyName(string $propertyName)
    {
        $this->propertyName = $propertyName;
    }

    /**
     * If the given value is empty
     *
     * @param string $value The value
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
    }
}
