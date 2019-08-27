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
(function (factory) { 'function' === typeof define && define.amd ? define('map', ['jquery'], factory) : factory(jQuery) })(function ($) {
    "use strict";
    /**
     * Module: TYPO3/CMS/StoreFinder/FrontendMap
     * contains all logic for the frontend map output
     * @exports TYPO3/CMS/StoreFinder/FrontendMap
     */
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
                language: 'en',
                markerIcon: '',
                apiV3Layers: '',
                kmlUrl: '',
                renderSingleViewCallback: null,
                handleCloseButtonCallback: null
            };
            this.locations = locations;
            this.loadScript();
        }
        /**
         * Initialize map
         */
        FrontendMap.prototype.initializeMap = function () {
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
        FrontendMap.prototype.initializeLayer = function () {
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
        FrontendMap.prototype.showInformation = function (marker) {
            var location = marker.sfLocation;
            if (typeof this.mapConfiguration.renderSingleViewCallback === 'function') {
                this.mapConfiguration.renderSingleViewCallback(location, this.infoWindowTemplate);
            }
            else {
                this.infoWindow.close();
                this.infoWindow.setContent(this.infoWindowTemplate.render(location.information));
                this.infoWindow.setPosition(marker.getPosition());
                this.infoWindow.open(this.map, marker);
            }
        };
        /**
         * Process single location
         */
        FrontendMap.prototype.processLocation = function (location) {
            var _this = this;
            var markerArguments = {
                title: location.name,
                position: new window.google.maps.LatLng(location.lat, location.lng),
                icon: ''
            };
            this.locationIndex++;
            location.information.index = this.locationIndex;
            if (location.information.icon) {
                markerArguments.icon = location.information.icon;
            }
            else if (this.mapConfiguration.hasOwnProperty('markerIcon')) {
                markerArguments.icon = this.mapConfiguration.markerIcon;
            }
            var marker = new window.google.maps.Marker(markerArguments);
            marker.sfLocation = location;
            marker.setMap(this.map);
            window.google.maps.event.addListener(marker, 'click', function (event, marker) {
                _this.showInformation(marker);
            });
            // attach marker to location to be able to close it later
            location.marker = marker;
        };
        /**
         * Initialize location marker on map
         */
        FrontendMap.prototype.initializeLocations = function () {
            this.locations.map(this.processLocation.bind(this));
        };
        /**
         * Initialize instance of map infoWindow
         */
        FrontendMap.prototype.initializeInfoWindow = function () {
            this.infoWindow = new window.google.maps.InfoWindow();
        };
        /**
         * Initialize info window template
         */
        FrontendMap.prototype.initializeTemplates = function () {
            var _this = this;
            this.infoWindowTemplate = window.Hogan.compile($('#templateInfoWindow').html());
            $(document).on('click', '.tx-storefinder .infoWindow .close', function (event, $closeButton) {
                if (typeof _this.mapConfiguration.renderSingleViewCallback === 'function') {
                    _this.mapConfiguration.handleCloseButtonCallback($closeButton);
                }
                else {
                    _this.infoWindow.close();
                }
            });
        };
        /**
         * Trigger click event on marker on click in result list
         */
        FrontendMap.prototype.openInfoWindow = function (index) {
            window.google.maps.event.trigger(this.locations[index].marker, 'click');
        };
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
         * Post load google map script processing
         */
        FrontendMap.prototype.postLoadScript = function () {
            this.initializeMap();
            this.initializeLayer();
            this.initializeLocations();
            this.initializeInfoWindow();
            this.initializeTemplates();
            this.initializeListEvents();
        };
        /**
         * Load google map script
         */
        FrontendMap.prototype.loadScript = function () {
            var self = this, apiUrl = 'https://maps.googleapis.com/maps/api/js?v=3.exp', parameter = '&key=' + this.mapConfiguration.apiConsoleKey
                + '&sensor=' + (this.mapConfiguration.allowSensors ? 'true' : 'false');
            if (self.mapConfiguration.language !== '') {
                parameter += '&language=' + self.mapConfiguration.language;
            }
            if (self.mapConfiguration.hasOwnProperty('apiUrl')) {
                apiUrl = self.mapConfiguration.apiUrl;
            }
            $.when($.getScript(apiUrl + parameter)).done(function () {
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
                console.log('Failed loading google maps resources.');
            });
        };
        return FrontendMap;
    }());
    $(document).ready(function () {
        if (typeof window.mapConfiguration == 'object' && window.mapConfiguration.active) {
            // make module public to be available for callback after load
            window.StoreFinder = new FrontendMap(window.mapConfiguration, window.locations);
        }
    });
    return FrontendMap;
});

//# sourceMappingURL=FrontendMap.js.map
