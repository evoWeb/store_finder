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

interface SettableInterface
{
    /**
     * Setter for model
     *
     * @param \Evoweb\StoreFinder\Domain\Model\Constraint $model
     */
    public function setModel($model);

    /**
     * Setter for propertyName
     *
     * @param string $propertyName
     */
    public function setPropertyName(string $propertyName);
}
