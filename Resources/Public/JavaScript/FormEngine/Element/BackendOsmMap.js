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
define(["require", "exports", "jquery", "TYPO3/CMS/StoreFinder/Vendor/Leaflet/leaflet"], function (require, exports, $, L) {
    "use strict";
    var BackendOsmMap = /** @class */ (function () {
        function BackendOsmMap(options) {
            var _this = this;
            this.mapConfiguration = {
                uid: '0',
                latitude: 0,
                longitude: 0,
                zoom: 15
            };
            this.mapConfiguration = options;
            this.initializeMap();
            this.initializeMarker();
            this.initializeEvents();
            setTimeout(function () { _this.map.invalidateSize(); }, 10);
        }
        BackendOsmMap.prototype.initializeMap = function () {
            this.map = L.map('map');
            this.map.setView([this.mapConfiguration.latitude, this.mapConfiguration.longitude], this.mapConfiguration.zoom);
            this.map.doubleClickZoom.disable();
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(this.map);
        };
        BackendOsmMap.prototype.initializeMarker = function () {
            var options = {
                draggable: true
            };
            this.marker = new L.Marker([this.mapConfiguration.latitude, this.mapConfiguration.longitude], options);
            this.marker.bindPopup('').addTo(this.map);
        };
        BackendOsmMap.prototype.initializeEvents = function () {
            var _this = this;
            $('.t3js-tabmenu-item a').bind('click', function (event) {
                $('#' + $(event.target).attr('aria-controls')).trigger('cssActiveAdded');
            });
            $('#map').parents('.tab-pane').on('cssActiveAdded', function () {
                setTimeout(function () { _this.map.invalidateSize(); }, 10);
            });
            this.map.on('dblclick', function (event) {
                var coordinates = event.latlng;
                _this.marker.setLatLng(coordinates);
                _this.updateCoordinateFields(coordinates, _this);
                return false;
            });
            this.marker.on('moveend', function (event) {
                var coordinates = event.target.getLatLng();
                _this.updateCoordinateFields(coordinates, _this);
            });
        };
        BackendOsmMap.prototype.updateCoordinateFields = function (coordinates, backend) {
            var fieldPrefix = 'data[tx_storefinder_domain_model_location][' + backend.mapConfiguration.uid + ']', $latitudeField = $('*[data-formengine-input-name="' + fieldPrefix + '[latitude]"]'), $longitudeField = $('*[data-formengine-input-name="' + fieldPrefix + '[longitude]"]');
            $latitudeField.val(coordinates.lat).trigger('change');
            $longitudeField.val(coordinates.lng).trigger('change');
        };
        return BackendOsmMap;
    }());
    return BackendOsmMap;
});

//# sourceMappingURL=BackendOsmMap.js.map
