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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;

/**
 * A either validator to check that a value is set
 */
class EitherValidator extends AbstractValidator implements ValidatorInterface
{
    /**
     * @var array
     */
    protected $supportedOptions = [
        'properties' => [false, 'Properties to check in either', 'string'],
    ];

    protected array $properties = [];

    protected ?Constraint $model;

    protected string $propertyName;

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

    public function setModel(Constraint $model)
    {
        $this->model = $model;
    }

    public function setPropertyName(string $propertyName)
    {
        $this->propertyName = $propertyName;
    }

    /**
     * If the given value is empty
     *
     * @param string $value The value
     */
    protected function isValid($value)
    {
        $result = false;

        if (!empty($value)) {
            $result = true;
        } else {
            $properties = array_diff($this->properties, [$this->propertyName]);
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
                LocalizationUtility::translate('error_either', 'StoreFinder'),
                1305008423
            );
        }
    }
}
