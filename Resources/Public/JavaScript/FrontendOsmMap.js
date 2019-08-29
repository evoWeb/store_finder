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
(function (factory) { 'function' === typeof define && define.amd ? define('map', ['jquery', 'leaflet'], factory) : factory(jQuery, L) })(function ($, L) {
    "use strict";
    var Marker = /** @class */ (function (_super) {
        __extends(Marker, _super);
        function Marker() {
            return _super !== null && _super.apply(this, arguments) || this;
        }
        return Marker;
    }(L.Marker));
    /**
     * Module: TYPO3/CMS/StoreFinder/FrontendOsmMap
     * contains all logic for the frontend map output
     * @exports TYPO3/CMS/StoreFinder/FrontendOsmMap
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
            var self = this;
            self.map = L.map('tx_storefinder_map');
            if (typeof this.mapConfiguration.center !== 'undefined') {
                this.map.setView([self.mapConfiguration.center.lat, self.mapConfiguration.center.lng], parseInt(self.mapConfiguration.zoom, 10));
            }
            else {
                this.map.setView([0, 0], 13);
            }
            // more providers can be found here http://leaflet-extras.github.io/leaflet-providers/preview/
            L.tileLayer('https://korona.geog.uni-heidelberg.de/tiles/roads/x={x}&y={y}&z={z}', {
                maxZoom: 20,
                attribution: 'Imagery from <a href="http://giscience.uni-hd.de/">GIScience Research Group @ University of Heidelberg</a> &mdash; Map data &copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(self.map);
        };
        /**
         * Initialize information layer on map
         */
        FrontendMap.prototype.initializeLayer = function () {
            /*if (this.mapConfiguration.apiV3Layers.indexOf('traffic') > -1) {
              let trafficLayer = new google.maps.TrafficLayer();
              trafficLayer.setMap(this.map);
            }

            if (this.mapConfiguration.apiV3Layers.indexOf('bicycling') > -1) {
              let bicyclingLayer = new google.maps.BicyclingLayer();
              bicyclingLayer.setMap(this.map);
            }

            if (this.mapConfiguration.apiV3Layers.indexOf('panoramio') > -1) {
              let panoramioLayer = new google.maps.panoramio.PanoramioLayer();
              panoramioLayer.setMap(this.map);
            }

            if (this.mapConfiguration.apiV3Layers.indexOf('weather') > -1) {
              let weatherLayer = new google.maps.weather.WeatherLayer({
                temperatureUnits: google.maps.weather.TemperatureUnit.DEGREE
              });
              weatherLayer.setMap(this.map);
            }*/
            if (this.mapConfiguration.apiV3Layers.indexOf('kml') > -1) {
                var $jsDeferred = $.Deferred(), $jsFile = $('<script/>', {
                    src: 'https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-omnivore/v0.3.1/leaflet-omnivore.min.js',
                    crossorigin: ''
                }).appendTo('head');
                $jsDeferred.resolve($jsFile);
                var self_1 = this;
                $.when($jsDeferred.promise()).done(function () {
                    var kmlLayer = omnivore.kml(self_1.mapConfiguration.kmlUrl);
                    kmlLayer.setMap(self_1.map);
                }).fail(function () {
                    console.log('Failed loading resources.');
                });
            }
        };
        /**
         * Close previously open info window, renders new content and opens the window
         *
         * @param {object} marker
         */
        FrontendMap.prototype.showInformation = function (marker) {
            var location = marker.sfLocation;
            if (typeof this.mapConfiguration.renderSingleViewCallback === 'function') {
                this.mapConfiguration.renderSingleViewCallback(location, this.infoWindowTemplate);
            }
            else {
                if (this.infoWindow.isOpen()) {
                    this.infoWindow.closePopup();
                }
                this.infoWindow = marker.getPopup()
                    .setContent(this.infoWindowTemplate.render(location.information))
                    .setLatLng(L.latLng(location.lat, location.lng))
                    .openOn(this.map);
            }
        };
        /**
         * Process single location
         *
         * @param location
         */
        FrontendMap.prototype.processLocation = function (location) {
            var _this = this;
            var icon = '';
            if (location.information.icon) {
                icon = location.information.icon;
            }
            else if (this.mapConfiguration.hasOwnProperty('markerIcon')) {
                icon = this.mapConfiguration.markerIcon;
            }
            this.locationIndex++;
            location.information.index = this.locationIndex;
            var marker = new Marker([location.lat, location.lng], {
                title: location.name,
                icon: L.icon({ iconUrl: icon })
            });
            marker.sfLocation = location;
            marker.bindPopup('').addTo(this.map);
            marker.on('click', function () {
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
            this.infoWindow = L.popup();
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
                    _this.infoWindow.closePopup();
                }
            });
        };
        /**
         * Trigger click event on marker on click in result list
         */
        FrontendMap.prototype.openInfoWindow = function (index) {
            this.locations[index].marker.fire('click');
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
        /**
         * Load open street map leaflet script
         */
        FrontendMap.prototype.loadScript = function () {
            var self = this, $cssDeferred = $.Deferred(), $cssFile = $('<link/>', {
                rel: 'stylesheet',
                type: 'text/css',
                href: 'https://unpkg.com/leaflet@1.3.4/dist/leaflet.css',
                integrity: 'sha512-puBpdR0798OZvTTbP4A8Ix/l+A4dHDD0DGqYW6RQ+9jxkRFclaxxQb/SJAWZfWAkuyeQUytO7+7N4QKrDh+drA==',
                crossorigin: ''
            }).appendTo('head'), $jsDeferred = $.Deferred(), $jsFile = $('<script/>', {
                src: 'https://unpkg.com/leaflet@1.3.4/dist/leaflet.js',
                integrity: 'sha512-nMMmRyTVoLYqjP9hrbed9S+FzjZHW5gY1TWCHA5ckwXZBadntCNs8kEqAWdrb9O7rxbCaA4lKTIWjDXZxflOcA==',
                crossorigin: ''
            }).appendTo('head');
            $cssDeferred.resolve($cssFile);
            $jsDeferred.resolve($jsFile);
            $.when($cssDeferred.promise(), $jsDeferred.promise()).done(function () {
                function wait() {
                    if (typeof window.L !== 'undefined') {
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

//# sourceMappingURL=FrontendOsmMap.js.map
