<?php
namespace Evoweb\StoreFinder\Validation;

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

/**
 * Validator resolver to automatically find a validator for a given subject
 */
class ValidatorResolver extends \TYPO3\CMS\Extbase\Validation\ValidatorResolver
{
    /**
     * Get the parsed options given in @validate annotations.
     *
     * @param string $validateValue
     *
     * @return array
     */
    public function getParsedValidatorAnnotation(string $validateValue): array
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        return $this->parseValidatorAnnotation($validateValue);
    }
}
