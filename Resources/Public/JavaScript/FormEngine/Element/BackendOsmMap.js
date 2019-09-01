/**
 * This file is developed by evoweb.
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
            this.initializeEvents();
            this.createMarker();
            setTimeout(function () { _this.map.invalidateSize(); }, 10);
        }
        BackendOsmMap.prototype.initializeMap = function () {
            this.map = L.map('map');
            this.map.setView([this.mapConfiguration.latitude, this.mapConfiguration.longitude], this.mapConfiguration.zoom);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(this.map);
        };
        BackendOsmMap.prototype.initializeEvents = function () {
            var _this = this;
            $('.t3js-tabmenu-item a').bind('click', function (event) {
                $('#' + $(event.target).attr('aria-controls')).trigger('cssActiveAdded');
            });
            $('#map').parents('.tab-pane').on('cssActiveAdded', function () {
                setTimeout(function () { _this.map.invalidateSize(); }, 10);
            });
        };
        BackendOsmMap.prototype.createMarker = function () {
            var _this = this;
            var options = {
                draggable: true
            }, marker = new L.Marker([this.mapConfiguration.latitude, this.mapConfiguration.longitude], options);
            marker.bindPopup('').addTo(this.map);
            marker.on('moveend', function (event) { _this.updateCoordinateFields(event.target, _this); });
        };
        BackendOsmMap.prototype.updateCoordinateFields = function (movedMarker, backend) {
            var coordinates = movedMarker.getLatLng(), fieldPrefix = 'data[tx_storefinder_domain_model_location][' + backend.mapConfiguration.uid + ']', $latitudeField = $('*[data-formengine-input-name="' + fieldPrefix + '[latitude]"]'), $longitudeField = $('*[data-formengine-input-name="' + fieldPrefix + '[longitude]"]');
            $latitudeField.val(coordinates.lat).trigger('change');
            $longitudeField.val(coordinates.lng).trigger('change');
        };
        return BackendOsmMap;
    }());
    return BackendOsmMap;
});

//# sourceMappingURL=BackendOsmMap.js.map
