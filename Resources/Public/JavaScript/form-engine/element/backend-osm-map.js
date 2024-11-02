import * as L from '@evoweb/store-finder/leaflet/leaflet-src.esm.js';

/**
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
class BackendOsmMap {
    constructor(mapConfiguration) {
        this.mapConfiguration = {
            uid: '0',
            mapId: '',
            latitude: 0,
            longitude: 0,
            zoom: 16
        };
        this.mapConfiguration = mapConfiguration;
        this.initializeFields();
        this.initializeMap();
        this.initializeMarker();
        this.initializeEvents();
        this.resizeMap();
    }
    initializeFields() {
        const fieldPrefix = 'data[tx_storefinder_domain_model_location][' + this.mapConfiguration.uid + ']';
        this.latitudeField = document.querySelector('[data-formengine-input-name="' + fieldPrefix + '[latitude]"]');
        this.longitudeField = document.querySelector('[data-formengine-input-name="' + fieldPrefix + '[longitude]"]');
    }
    initializeMap() {
        this.map = L.map(this.mapConfiguration.mapId);
        this.map.setView(new L.LatLng(this.mapConfiguration.latitude, this.mapConfiguration.longitude), this.mapConfiguration.zoom);
        this.map.doubleClickZoom.disable();
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            minZoom: 8
        }).addTo(this.map);
    }
    initializeMarker() {
        const options = {
            draggable: true
        };
        this.marker = new L.Marker(new L.LatLng(this.mapConfiguration.latitude, this.mapConfiguration.longitude), options);
        this.marker.bindPopup('').addTo(this.map);
    }
    initializeEvents() {
        let mapParentTab = document.querySelector('#' + this.mapConfiguration.mapId);
        while ((mapParentTab = mapParentTab.parentElement) && mapParentTab.tagName != 'body') {
            if (mapParentTab.matches('.tab-pane')) {
                break;
            }
        }
        [...document.querySelectorAll('.t3js-tabmenu-item a')].forEach(tab => {
            tab.addEventListener('click', (event) => {
                if (mapParentTab.id === event.target.getAttribute('aria-controls')) {
                    this.resizeMap();
                }
            });
        });
        this.map.on('dblclick', (event) => {
            const coordinates = event.latlng;
            this.marker.setLatLng(coordinates);
            this.updateCoordinateFields(coordinates);
        });
        this.marker.on('moveend', (event) => {
            const coordinates = event.target.getLatLng();
            this.updateCoordinateFields(coordinates);
        });
    }
    updateCoordinateFields(coordinates) {
        this.latitudeField.value = coordinates.lat.toString();
        this.latitudeField.dispatchEvent(new Event('change', { bubbles: true }));
        this.longitudeField.value = coordinates.lng.toString();
        this.longitudeField.dispatchEvent(new Event('change', { bubbles: true }));
    }
    resizeMap() {
        setTimeout(() => { this.map.invalidateSize(); }, 10);
    }
}

export { BackendOsmMap as default };
//# sourceMappingURL=backend-osm-map.js.map
