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
var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
(function (factory) {
    'function' === typeof define && define.amd ?
        define('FrontendGoogleMap', ['jquery', 'FrontendMap'], factory) :
        factory(window, jQuery, window.FrontendMap)
})(function (window, $, FrontendMap_1) {
    "use strict";
    /**
     * Module: TYPO3/CMS/StoreFinder/FrontendGoogleMap
     * contains all logic for the frontend map output
     * @exports TYPO3/CMS/StoreFinder/FrontendGoogleMap
     */
    var FrontendGoogleMap = /** @class */ (function (_super) {
        __extends(FrontendGoogleMap, _super);
        function FrontendGoogleMap() {
            return _super !== null && _super.apply(this, arguments) || this;
        }
        /**
         * Initialize map
         */
        FrontendGoogleMap.prototype.initializeMap = function () {
            var _this = this;
            var center;
            window.google.maps.visualRefresh = true;
            if (typeof this.mapConfiguration.center !== 'undefined') {
                center = new window.google.maps.LatLng(this.mapConfiguration.center.lat, this.mapConfiguration.center.lng);
            }
            else {
                center = new window.google.maps.LatLng(0, 0);
            }
            var mapOptions = {
                zoom: parseInt(this.mapConfiguration.zoom, 10),
                center: center,
                disableDefaultUI: true,
                zoomControl: true,
                zoomControlOptions: {
                    style: window.google.maps.ZoomControlStyle.LARGE
                }
            };
            this.map = new window.google.maps.Map($('#tx_storefinder_map')[0], mapOptions);
            if (this.mapConfiguration.afterSearch === 0 && navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    var pos = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    _this.map.setCenter(pos);
                });
            }
        };
        /**
         * Initialize information layer on map
         */
        FrontendGoogleMap.prototype.initializeLayer = function () {
            if (this.mapConfiguration.apiV3Layers.indexOf('traffic') > -1) {
                var trafficLayer = new window.google.maps.TrafficLayer();
                trafficLayer.setMap(this.map);
            }
            if (this.mapConfiguration.apiV3Layers.indexOf('bicycling') > -1) {
                var bicyclingLayer = new window.google.maps.BicyclingLayer();
                bicyclingLayer.setMap(this.map);
            }
            if (this.mapConfiguration.apiV3Layers.indexOf('panoramio') > -1) {
                var panoramioLayer = new window.google.maps.panoramio.PanoramioLayer();
                panoramioLayer.setMap(this.map);
            }
            if (this.mapConfiguration.apiV3Layers.indexOf('weather') > -1) {
                var weatherLayer = new window.google.maps.weather.WeatherLayer({
                    temperatureUnits: window.google.maps.weather.TemperatureUnit.DEGREE
                });
                weatherLayer.setMap(this.map);
            }
            if (this.mapConfiguration.apiV3Layers.indexOf('kml') > -1) {
                var kmlLayer = new window.google.maps.KmlLayer(this.mapConfiguration.kmlUrl);
                kmlLayer.setMap(this.map);
            }
        };
        /**
         * Close previously open info window, renders new content and opens the window
         */
        FrontendGoogleMap.prototype.showInformation = function (location, marker) {
            if (typeof this.mapConfiguration.renderSingleViewCallback === 'function') {
                this.mapConfiguration.renderSingleViewCallback(location, this.infoWindowTemplate);
            }
            else {
                this.infoWindow.close();
                this.infoWindow.setContent(this.renderInfoWindowContent(location));
                this.infoWindow.setPosition(marker.getPosition());
                this.infoWindow.open(this.map, marker);
            }
        };
        /**
         * Create marker and add to map
         */
        FrontendGoogleMap.prototype.createMarker = function (location, icon) {
            var _this = this;
            var marker = new window.google.maps.Marker({
                title: location.name,
                position: new window.google.maps.LatLng(location.lat, location.lng),
                icon: icon
            });
            marker.setMap(this.map);
            window.google.maps.event.addListener(marker, 'click', function () {
                _this.showInformation(location, marker);
            });
            return marker;
        };
        /**
         * Initialize instance of map infoWindow
         */
        FrontendGoogleMap.prototype.initializeInfoWindow = function () {
            this.infoWindow = new window.google.maps.InfoWindow();
        };
        /**
         * Close info window
         */
        FrontendGoogleMap.prototype.closeInfoWindow = function () {
            this.infoWindow.close();
        };
        /**
         * Trigger click event on marker on click in result list
         */
        FrontendGoogleMap.prototype.openInfoWindow = function (index) {
            window.google.maps.event.trigger(this.locations[index].marker, 'click');
        };
        /**
         * Load google map script
         */
        FrontendGoogleMap.prototype.loadScript = function () {
            var self = this, apiUrl = 'https://maps.googleapis.com/maps/api/js?v=3.exp', parameter = '&key=' + this.mapConfiguration.apiConsoleKey
                + '&sensor=' + (this.mapConfiguration.allowSensors ? 'true' : 'false');
            if (self.mapConfiguration.language !== '') {
                parameter += '&language=' + self.mapConfiguration.language;
            }
            if (self.mapConfiguration.hasOwnProperty('apiUrl')) {
                apiUrl = self.mapConfiguration.apiUrl;
            }
            var $jsDeferred = $.Deferred(), $jsFile = $('<script/>', {
                src: apiUrl + parameter,
                crossorigin: ''
            }).appendTo('head');
            $jsDeferred.resolve($jsFile);
            $.when($jsDeferred.promise()).done(function () {
                function wait() {
                    if (typeof window.google !== 'undefined') {
                        this.postLoadScript();
                    }
                    else {
                        window.requestAnimationFrame(wait.bind(this));
                    }
                }
                window.requestAnimationFrame(wait.bind(self));
            }).fail(function () {
                console.log('Failed loading resources.');
            });
        };
        return FrontendGoogleMap;
    }(FrontendMap_1["default"]));
    $(document).ready(function () {
        if (typeof window.mapConfiguration == 'object' && window.mapConfiguration.active) {
            // make module public to be available for callback after load
            window.StoreFinder = new FrontendGoogleMap(window.mapConfiguration, window.locations);
        }
    });
    window.FrontendGoogleMap = {default: FrontendGoogleMap};
    return FrontendGoogleMap;
});

//# sourceMappingURL=FrontendGoogleMap.js.map
