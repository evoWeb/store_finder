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

namespace Evoweb\StoreFinder\Validation\Validator;

use Evoweb\StoreFinder\Domain\Model\Constraint;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;

/**
 * A validator to check that a value is set in either of the given properties
 *
 * country = "Evoweb.StoreFinder:Either(properties: 'city, zipcode')"
 */
class EitherValidator extends AbstractValidator implements ValidatorInterface
{
    /**
     * @var bool
     */
    protected $acceptsEmptyValues = false;

    protected $supportedOptions = [
        'properties' => [ '', 'Properties to check in either', 'string' ],
    ];

    protected array $properties = [];

    protected ?Constraint $model = null;

    protected string $propertyName = '';

    public function setOptions(array $options = []): void
    {
        if (isset($this->options['properties'])) {
            $this->properties = GeneralUtility::trimExplode(',', $this->options['properties'], true);
        }
    }

    public function setModel(Constraint $model): void
    {
        $this->model = $model;
    }

    public function setPropertyName(string $propertyName): void
    {
        $this->propertyName = $propertyName;
    }

    /**
     * Check if $value is valid. If it is not valid, needs to add an error to result.
     */
    protected function isValid(mixed $value): void
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
