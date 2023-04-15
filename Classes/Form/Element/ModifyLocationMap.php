<?php

declare(strict_types=1);

namespace Evoweb\StoreFinder\Form\Element;

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

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Utility\StringUtility;

class ModifyLocationMap extends AbstractFormElement
{
    public function render(): array
    {
        try {
            $configuration = (new ExtensionConfiguration())->get('store_finder');
        } catch (\Exception) {
            $configuration = [];
        }

        $fieldId = StringUtility::getUniqueId('formengine-map-');

        $row = $this->data['databaseRow'];

        $resultArray = $this->initializeResultArray();

        $resultArray['html'] = '<div id="' . $fieldId . '" style="height: 300px; width: 100%;"></div>';
        $resultArray['stylesheetFiles'][] = 'EXT:store_finder/Resources/Public/JavaScript/leaflet/leaflet.css';
        $resultArray['javaScriptModules']['modifyLocationMap'] = JavaScriptModuleInstruction::create(
            '@evoweb/store-finder/form-engine/element/backend-osm-map.js'
        )->instance([
            'mapId' => $fieldId,
            'uid' => $row['uid'],
            'latitude' => (float)($row['latitude'] ?: $configuration['latitude'] ?? 51.4583912),
            'longitude' => (float)($row['longitude'] ?: $configuration['longitude'] ?? 7.0157931),
            'zoom' => (int)($configuration['zoom'] ?? 16)
        ]);

        return $resultArray;
    }
}
