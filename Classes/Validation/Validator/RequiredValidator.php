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

/**
 * A required validator to check that a value is set
 */
class RequiredValidator extends Validator\AbstractValidator implements Validator\ValidatorInterface
{
    /**
     * @var bool
     */
    protected $acceptsEmptyValues = false;

    /**
     * If the given value is empty
     *
     * @param string $value The value
     */
    public function isValid($value)
    {
        if (empty($value)) {
            $this->addError(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('error_required', 'StoreFinder'),
                1305008423
            );
        }
    }
}
