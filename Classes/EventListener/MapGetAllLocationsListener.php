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

namespace Evoweb\StoreFinder\EventListener;

use Evoweb\StoreFinder\Controller\Event\MapGetLocationsByConstraintsEvent;
use Evoweb\StoreFinder\Domain\Model\Constraint;
use Evoweb\StoreFinder\Domain\Repository\LocationRepository;
use TYPO3\CMS\Core\Attribute\AsEventListener;

class MapGetAllLocationsListener
{
    protected LocationRepository $locationRepository;

    public function __construct(LocationRepository $locationRepository)
    {
        $this->locationRepository = $locationRepository;
    }

    // #[AsEventListener('storefinder_controller_locationsfetched', MapGetLocationsByConstraintsEvent::class)]
    public function onLocationsFetchedEvent(MapGetLocationsByConstraintsEvent $event): void
    {
        if ($this->isOverrideLocations($event)) {
            $event->setLocations($this->locationRepository->findAll());
        }
    }

    // #[AsEventListener('storefinder_controller_isoverride', MapGetLocationsByConstraintsEvent::class)]
    public function isOverrideLocations(MapGetLocationsByConstraintsEvent $event): bool
    {
        // @extensionScannerIgnoreLine
        $controller = $event->getController();
        $constraint = $controller->getArguments()->hasArgument('constraint') ?
            $controller->getArguments()->getArgument('constraint')->getValue() :
            null;

        return !(
            $constraint instanceof Constraint
            && (
                $constraint->getCity() !== ''
                || $constraint->getZipcode() !== ''
            )
        );
    }
}
