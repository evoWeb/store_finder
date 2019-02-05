<?php
namespace Evoweb\StoreFinder\Form\Element;

use TYPO3\CMS\Core\Page\PageRenderer;

class ModifyLocationMap extends \TYPO3\CMS\Backend\Form\Element\AbstractFormElement
{
    public function render()
    {
        $resultArray = $this->initializeResultArray();
        return $resultArray = $this->renderMap($resultArray);
    }

    protected function renderMap($resultArray)
    {
        /** @var PageRenderer $pageRenderer */
        $pageRenderer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->addRequireJsConfiguration(['paths' => [
            'TYPO3/CMS/StoreFinder/Leaflet' => 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.4.0/leaflet'
        ]]);

        $row = $this->data['databaseRow'];
        $latitude = !empty($row['latitude']) ? $row['latitude'] : '51.4583912';
        $longitude = !empty($row['longitude']) ? $row['longitude'] : '7.0157931';

        $resultArray['stylesheetFiles'][] = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.4.0/leaflet.css';
        $resultArray['requireJsModules']['modifyLocationMap'] = [
            'TYPO3/CMS/StoreFinder/FormEngine/Element/LocationMap' => 'function(LocationMap) {
                new LocationMap({
                    uid: \'' . $row['uid'] . '\',
                    latitude: \'' . $latitude . '\',
                    longitude: \'' . $longitude . '\'
                });
            }'
        ];
        $resultArray['html'] = '<div id="map" style="height: 240px; width: 100%;"></div>';

        return $resultArray;
    }
}
