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

class ModifyLocationMap extends \TYPO3\CMS\Backend\Form\Element\AbstractFormElement
{
    public function render(): array
    {
        $resultArray = $this->initializeResultArray();
        return $resultArray = $this->renderMap($resultArray);
    }

    protected function renderMap(array $resultArray): array
    {
        $row = $this->data['databaseRow'];
        $latitude = (float) ($row['latitude'] ? $row['latitude'] : 51.4583912);
        $longitude = (float) ($row['longitude'] ? $row['longitude'] : 7.0157931);

        $resultArray['html'] = '<div id="map" style="height: 300px; width: 100%;"></div>';
        $resultArray['stylesheetFiles'][] = 'EXT:store_finder/Resources/Public/JavaScript/Vendor/Leaflet/leaflet.css';
        $resultArray['requireJsModules']['modifyLocationMap'] = [
            'TYPO3/CMS/StoreFinder/FormEngine/Element/BackendOsmMap' => 'function(LocationMap) {
                new LocationMap({
                    uid: \'' . $row['uid'] . '\',
                    latitude: ' . $latitude . ',
                    longitude: ' . $longitude . ',
                    zoom: 15
                });
            }'
        ];

        return $resultArray;
    }
}
