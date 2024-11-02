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

use Evoweb\StoreFinder\Middleware\Event\ModifyMiddlewareLocationsEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class ModifyMiddlewareLocationsListener
{
    public function __construct(private ContentObjectRenderer $contentObjectRenderer) {}

    // #[AsEventListener('storefinder_middleware_locationsfetched', ModifyMiddlewareLocationsEvent::class)]
    public function __invoke(ModifyMiddlewareLocationsEvent $event): void
    {
        /* do what ever you need to change */
        $this->contentObjectRenderer->setRequest($event->getRequest());

        $locations = $event->getLocations();
        foreach ($locations as &$location) {
            if (!empty($location['notes'])) {
                $this->contentObjectRenderer->start($location, 'tx_storefinder_domain_model_location');
                $location['notes'] = $this->contentObjectRenderer->cObjGet([], 'notes');
            }
        }

        $event->setLocations($locations);
    }
}
