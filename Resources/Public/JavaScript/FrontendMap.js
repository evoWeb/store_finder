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
(function (factory) {
    'function' === typeof define && define.amd ?
        define('FrontendMap', ['mustache', 'jquery'], factory) :
        factory(window, Mustache, jQuery)
})(function (window, Mustache, $) {
    "use strict";
    var FrontendMap = /** @class */ (function () {
        /**
         * The constructor, set the class properties default values
         */
        function FrontendMap(mapConfiguration, locations) {
            this.locationIndex = 0;
            this.mapConfiguration = mapConfiguration || {
                active: false,
                afterSearch: 0,
                apiConsoleKey: '',
                apiUrl: '',
                allowSensors: false,
                language: '',
                markerIcon: '',
                apiV3Layers: '',
                kmlUrl: '',
                renderSingleViewCallback: null,
                handleCloseButtonCallback: null
            };
            this.locations = locations;
            this.loadScript();
        }
        FrontendMap.prototype.initializeMap = function () { };
        FrontendMap.prototype.initializeLayer = function () { };
        /**
         * Render content of the info window
         */
        FrontendMap.prototype.renderInfoWindowContent = function (location) {
            return Mustache.render(this.infoWindowTemplate, location.information);
        };
        FrontendMap.prototype.createMarker = function (location, icon) { };
        /**
         * Process single location
         *
         * @param location
         */
        FrontendMap.prototype.processLocation = function (location) {
            var icon = '';
            if (location.information.icon) {
                icon = location.information.icon;
            }
            else if (this.mapConfiguration.hasOwnProperty('markerIcon')) {
                icon = this.mapConfiguration.markerIcon;
            }
            this.locationIndex++;
            location.information.index = this.locationIndex;
            location.marker = this.createMarker(location, icon);
        };
        /**
         * Initialize location marker on map
         */
        FrontendMap.prototype.initializeLocations = function () {
            this.locations.map(this.processLocation.bind(this));
        };
        FrontendMap.prototype.initializeInfoWindow = function () { };
        FrontendMap.prototype.closeInfoWindow = function () { };
        FrontendMap.prototype.openInfoWindow = function (index) { };
        /**
         * Initialize list click events
         */
        FrontendMap.prototype.initializeListEvents = function () {
            var _this = this;
            $(document).on('click', '.tx-storefinder .resultList > li', function (event, $field) {
                _this.openInfoWindow($field.data('index'));
            });
        };
        /**
         * Initialize info window template
         */
        FrontendMap.prototype.initializeTemplates = function () {
            var _this = this;
            this.infoWindowTemplate = $('#templateInfoWindow').html();
            Mustache.parse(this.infoWindowTemplate);
            $(document).on('click', '.tx-storefinder .infoWindow .close', function (event, $closeButton) {
                if (typeof _this.mapConfiguration.renderSingleViewCallback === 'function') {
                    _this.mapConfiguration.handleCloseButtonCallback($closeButton);
                }
                else {
                    _this.closeInfoWindow();
                }
            });
        };
        /**
         * Post load javascript files
         */
        FrontendMap.prototype.postLoadScript = function () {
            this.initializeMap();
            this.initializeLayer();
            this.initializeLocations();
            this.initializeInfoWindow();
            this.initializeTemplates();
            this.initializeListEvents();
        };
        FrontendMap.prototype.loadScript = function () { };
        return FrontendMap;
    }());
    window.FrontendMap = {default: FrontendMap};
    return FrontendMap;
});

//# sourceMappingURL=FrontendMap.js.map
