<?php

declare(strict_types=1);

namespace Evoweb\StoreFinder\EventListener;

use Evoweb\StoreFinder\Middleware\Event\ModifyMiddlewareLocationsEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class ModifyMiddlewareLocationsEventListener
{
    // #[AsEventListener('storefinder_middleware_locationsfetched', ModifyMiddlewareLocationsEvent::class)]
    public function __invoke(ModifyMiddlewareLocationsEvent $event): void
    {
        /* do what ever you need to change */
        /** @var ContentObjectRenderer $contentObjectRenderer */
        $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $contentObjectRenderer->setRequest($event->getRequest());

        $locations = $event->getLocations();
        $table = 'tx_storefinder_domain_model_location';

        foreach ($locations as &$location) {
            if (!empty($location['notes'])) {
                $contentObjectRenderer->start($location, $table);
                $location['notes'] = $contentObjectRenderer->cObjGet([], 'notes');
            }
        }

        $event->setLocations($locations);
    }
}
