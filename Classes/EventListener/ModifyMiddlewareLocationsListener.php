<?php

declare(strict_types=1);

namespace Evoweb\StoreFinder\EventListener;

use Evoweb\StoreFinder\Event\ModifyMiddlewareLocationsEvent;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\ProcessedFile;
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
        foreach ($locations as &$location) {
            if (!empty($location['notes'])) {
                $contentObject->start($location, $table);
                $location['notes'] = $contentObject->parseFunc(
                    $location['notes'],
                    null,
                    '< ' . $settings['tables'][$table]['parseFuncTSPath']
                );
            }
        }

        $event->setLocations($locations);
    }
}
