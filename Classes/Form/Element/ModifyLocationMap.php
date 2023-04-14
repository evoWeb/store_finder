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
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Utility\StringUtility;

class ModifyLocationMap extends AbstractFormElement
{
    public function render(): array
    {
        $row = $this->data['databaseRow'];
        $latitude = (float)($row['latitude'] ?: 51.4583912);
        $longitude = (float)($row['longitude'] ?: 7.0157931);
        $fieldId = StringUtility::getUniqueId('formengine-map-');

        $resultArray = $this->initializeResultArray();

        $resultArray['html'] = '<div id="' . $fieldId . '" style="height: 300px; width: 100%;"></div>';
        $resultArray['stylesheetFiles'][] = 'EXT:store_finder/Resources/Public/JavaScript/leaflet/leaflet.css';
        $resultArray['javaScriptModules']['modifyLocationMap'] = JavaScriptModuleInstruction::create(
            '@evoweb/store-finder/form-engine/element/backend-osm-map.js'
        )->instance([
            'uid' => $row['uid'],
            'mapId' => $fieldId,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'zoom' => 15
        ]);

        return $resultArray;
    }
}
