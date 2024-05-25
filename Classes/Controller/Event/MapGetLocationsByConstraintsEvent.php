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

namespace Evoweb\StoreFinder\Controller\Event;

use Evoweb\StoreFinder\Controller\MapController;
use Evoweb\StoreFinder\Domain\Model\Constraint;

class MapGetLocationsByConstraintsEvent
{
    public function __construct(
        protected MapController $controller,
        protected array $locations,
        protected Constraint $constraint
    ) {}

    public function getController(): MapController
    {
        return $this->controller;
    }

    public function getLocations(): array
    {
        return $this->locations;
    }

    public function setLocations(array $locations): void
    {
        $this->locations = $locations;
    }

    public function getConstraint(): Constraint
    {
        return $this->constraint;
    }
}
