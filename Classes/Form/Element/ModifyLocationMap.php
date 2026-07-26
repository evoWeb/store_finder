<?php

declare(strict_types=1);

/*
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Evoweb\StoreFinder\Form\Element;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Utility\StringUtility;

class ModifyLocationMap extends AbstractFormElement
{
    /**
     * @return array<string, mixed>
     */
    public function render(): array
    {
        try {
            $configuration = (new ExtensionConfiguration())->get('store_finder');
        } catch (\Exception) {
            $configuration = [];
        }
        /** @var array<string, mixed> $configuration */
        $configuration = is_array($configuration) ? $configuration : [];

        $fieldId = StringUtility::getUniqueId('formengine-map-');

        /** @var array<string, mixed> $row */
        $row = $this->data['databaseRow'];

        $resultArray = $this->initializeResultArray();

        $latitude = $row['latitude'] ?? null;
        if (empty($latitude)) {
            $latitude = $configuration['latitude'] ?? 51.4583912;
        }
        $longitude = $row['longitude'] ?? null;
        if (empty($longitude)) {
            $longitude = $configuration['longitude'] ?? 7.0157931;
        }

        $resultArray['html'] = '<div id="' . $fieldId . '" style="height: 300px; width: 100%;"></div>';
        $resultArray['stylesheetFiles'][] = 'EXT:store_finder/Resources/Public/JavaScript/leaflet/leaflet.css';
        $resultArray['javaScriptModules']['modifyLocationMap'] = JavaScriptModuleInstruction::create(
            '@evoweb/store-finder/form-engine/element/backend-osm-map.js'
        )->instance([
            'mapId' => $fieldId,
            'uid' => $row['uid'],
            'latitude' => $this->toFloatOrDefault($latitude, 51.4583912),
            'longitude' => $this->toFloatOrDefault($longitude, 7.0157931),
            'zoom' => $this->toIntOrDefault($configuration['zoom'] ?? 16, 16),
        ]);

        return $resultArray;
    }

    private function toFloatOrDefault(mixed $value, float $default = 0.0): float
    {
        return is_numeric($value) ? (float)$value : $default;
    }

    private function toIntOrDefault(mixed $value, int $default = 0): int
    {
        return is_numeric($value) ? (int)$value : $default;
    }
}
