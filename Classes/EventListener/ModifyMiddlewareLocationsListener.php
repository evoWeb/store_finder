<?php

declare(strict_types=1);

namespace Evoweb\StoreFinder\EventListener;

use Evoweb\StoreFinder\Event\ModifyMiddlewareLocationsEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class ModifyMiddlewareLocationsListener
{
    public function __invoke(ModifyMiddlewareLocationsEvent $event): void
    {
        $settings = $event->getSettings();
        $table = 'tx_storefinder_domain_model_location';
        /** @var ContentObjectRenderer $contentObject */
        $contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $contentObject->setRequest($event->getRequest());

        $locations = $event->getLocations();
        /* do what ever you need to change
        foreach ($locations as &$location) {
            if (!empty($location['notes'])) {
                $contentObject->start($location, $table);
                $location['notes'] = $contentObject->cObjGet([], 'notes');
            }
        }
        */

        $event->setLocations($locations);
    }
}
