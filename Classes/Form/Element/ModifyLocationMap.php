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
            'TYPO3/CMS/StoreFinder/Leaflet' => rtrim($this->getRelativeFilePath(
                'EXT:store_finder/Resources/Public/JavaScript/leaflet.js'
            ), '.js/')
        ]]);

        $row = $this->data['databaseRow'];
        $latitude = !empty($row['latitude']) ? $row['latitude'] : '51.4583912';
        $longitude = !empty($row['longitude']) ? $row['longitude'] : '7.0157931';

        $resultArray['stylesheetFiles'][] = rtrim($this->getRelativeFilePath(
            'EXT:store_finder/Resources/Public/Stylesheet/leaflet.css'
        ), '/');
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

    protected function getRelativeFilePath(string $filePath): string
    {
        return \TYPO3\CMS\Core\Utility\PathUtility::getRelativePath(
            \TYPO3\CMS\Core\Core\Environment::getPublicPath() . '/typo3/',
            \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($filePath)
        );
    }
}
