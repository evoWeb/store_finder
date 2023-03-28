<?php

declare(strict_types=1);

namespace Evoweb\StoreFinder\Controller\Event;

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

use Evoweb\StoreFinder\Controller\MapController;
use Evoweb\StoreFinder\Domain\Model\Constraint;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

class MapGetLocationsByConstraintsEvent
{
    public function __construct(
        protected MapController $controller,
        protected QueryResultInterface $locations,
        protected Constraint $constraint
    ) {
    }

    public function getController(): MapController
    {
        return $this->controller;
    }

    public function getLocations(): QueryResultInterface
    {
        return $this->locations;
    }

    public function setLocations(QueryResultInterface $locations): void
    {
        $this->locations = $locations;
    }

    public function getConstraint(): Constraint
    {
        return $this->constraint;
    }
}
