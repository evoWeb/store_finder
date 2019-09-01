<?php
declare(strict_types = 1);
namespace Evoweb\StoreFinder\Form\Element;

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
        $latitude = (float) ($row['latitude'] ?? 51.4583912);
        $longitude = (float) ($row['longitude'] ?? 7.0157931);

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
