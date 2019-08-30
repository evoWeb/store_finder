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

/// <reference types="../types/index" />
import * as Mustache from 'mustache';
import * as $ from 'jquery';
import * as L from 'leaflet';
import {MapConfiguration, Location} from "./Interfaces";

class Marker extends L.Marker {
  public sfLocation: Location;
}

/**
 * Module: TYPO3/CMS/StoreFinder/FrontendOsmMap
 * contains all logic for the frontend map output
 * @exports TYPO3/CMS/StoreFinder/FrontendOsmMap
 */
class FrontendMap {
  private map: L.Map;
  private mapConfiguration: MapConfiguration;
  private locations: Array<Location>;
  private locationIndex: number = 0;
  private infoWindow: L.Popup;
  private infoWindowTemplate: string;

  /**
   * The constructor, set the class properties default values
   */
  constructor(mapConfiguration: MapConfiguration, locations: Array<Location>) {
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
  initializeMap() {
    let self = this;

    self.map = L.map('tx_storefinder_map');

    if (typeof this.mapConfiguration.center !== 'undefined') {
      this.map.setView(
        [self.mapConfiguration.center.lat, self.mapConfiguration.center.lng],
        parseInt(self.mapConfiguration.zoom, 10)
      );
    } else {
      this.map.setView([0, 0], 13);
    }

    // more providers can be found here http://leaflet-extras.github.io/leaflet-providers/preview/
    L.tileLayer(
      'https://korona.geog.uni-heidelberg.de/tiles/roads/x={x}&y={y}&z={z}', {
        maxZoom: 20,
        attribution: 'Imagery from <a href="http://giscience.uni-hd.de/">GIScience Research Group @ University of Heidelberg</a> &mdash; Map data &copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
      }
    ).addTo(self.map);
  }

  /**
   * Initialize information layer on map
   */
  initializeLayer(this: FrontendMap) {
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
      let $jsDeferred = $.Deferred(),
        $jsFile = $('<script/>', {
          src: 'https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-omnivore/v0.3.1/leaflet-omnivore.min.js',
          crossorigin: ''
        }).appendTo('head');

      $jsDeferred.resolve($jsFile);

      let self = this;
      $.when($jsDeferred.promise()).done(function () {
        let kmlLayer = omnivore.kml(self.mapConfiguration.kmlUrl);
        kmlLayer.setMap(self.map);
      }).fail(function () {
        console.log('Failed loading resources.');
      });
    }
  }

  /**
   * Close previously open info window, renders new content and opens the window
   *
   * @param {object} marker
   */
  showInformation(this: FrontendMap, marker: Marker) {
    let location = marker.sfLocation;

    if (typeof this.mapConfiguration.renderSingleViewCallback === 'function') {
      this.mapConfiguration.renderSingleViewCallback(location, this.infoWindowTemplate);
    } else {
      if (this.infoWindow.isOpen()) {
        this.infoWindow.closePopup();
      }

      this.infoWindow = marker.getPopup()
        .setContent(Mustache.render(this.infoWindowTemplate, location.information))
        .setLatLng(L.latLng(location.lat, location.lng))
        .openOn(this.map);
    }
  }

  /**
   * Process single location
   *
   * @param location
   */
  processLocation(this: FrontendMap, location: Location) {
    let icon = '';
    if (location.information.icon) {
      icon = location.information.icon;
    } else if (this.mapConfiguration.hasOwnProperty('markerIcon')) {
      icon = this.mapConfiguration.markerIcon;
    }

    this.locationIndex++;
    location.information.index = this.locationIndex;

    let marker = new Marker([location.lat, location.lng], {
      title: location.name,
      icon: L.icon({iconUrl: icon}),
    });
    marker.sfLocation = location;
    marker.bindPopup('').addTo(this.map);

    marker.on('click', () => {
      this.showInformation(marker);
    });

    // attach marker to location to be able to close it later
    location.marker = marker;
  }

  /**
   * Initialize location marker on map
   */
  initializeLocations(this: FrontendMap) {
    this.locations.map(this.processLocation.bind(this));
  }

  /**
   * Initialize instance of map infoWindow
   */
  initializeInfoWindow() {
    this.infoWindow = L.popup();
  }

  /**
   * Initialize info window template
   */
  initializeTemplates(this: FrontendMap) {
    this.infoWindowTemplate = $('#templateInfoWindow').html();
    Mustache.parse(this.infoWindowTemplate);

    $(document).on('click', '.tx-storefinder .infoWindow .close', (event: Event, $closeButton: JQuery): void => {
      if (typeof this.mapConfiguration.renderSingleViewCallback === 'function') {
        this.mapConfiguration.handleCloseButtonCallback($closeButton);
      } else {
        this.infoWindow.closePopup();
      }
    });
  }

  /**
   * Trigger click event on marker on click in result list
   */
  openInfoWindow(this: FrontendMap, index: number) {
    this.locations[index].marker.fire('click');
  }

  /**
   * Initialize list click events
   */
  initializeListEvents(this: FrontendMap) {
    $(document).on('click', '.tx-storefinder .resultList > li', (event: Event, $field: JQuery): void => {
      this.openInfoWindow($field.data('index'));
    });
  }

  /**
   * Post load javascript files
   */
  postLoadScript() {
    this.initializeMap();
    this.initializeLayer();
    this.initializeLocations();
    this.initializeInfoWindow();
    this.initializeTemplates();
    this.initializeListEvents();
  }

  /**
   * Load open street map leaflet script
   */
  loadScript() {
    let self = this,
      $cssDeferred = $.Deferred(),
      $cssFile = $('<link/>', {
        rel: 'stylesheet',
        type: 'text/css',
        href: 'https://unpkg.com/leaflet@1.3.4/dist/leaflet.css',
        integrity: 'sha512-puBpdR0798OZvTTbP4A8Ix/l+A4dHDD0DGqYW6RQ+9jxkRFclaxxQb/SJAWZfWAkuyeQUytO7+7N4QKrDh+drA==',
        crossorigin: ''
      }).appendTo('head'),
      $jsDeferred = $.Deferred(),
      $jsFile = $('<script/>', {
        src: 'https://unpkg.com/leaflet@1.3.4/dist/leaflet.js',
        integrity: 'sha512-nMMmRyTVoLYqjP9hrbed9S+FzjZHW5gY1TWCHA5ckwXZBadntCNs8kEqAWdrb9O7rxbCaA4lKTIWjDXZxflOcA==',
        crossorigin: ''
      }).appendTo('head');

    $cssDeferred.resolve($cssFile);
    $jsDeferred.resolve($jsFile);

    $.when(
      $cssDeferred.promise(),
      $jsDeferred.promise()
    ).done(function () {
      function wait(this: FrontendMap) {
        if (typeof window.L !== 'undefined') {
          this.postLoadScript();
        } else {
          window.requestAnimationFrame(wait.bind(this));
        }
      }
      window.requestAnimationFrame(wait.bind(self));
    }).fail(function () {
      console.log('Failed loading resources.');
    });
  }
}

$(document).ready(function () {
  if (typeof window.mapConfiguration == 'object' && window.mapConfiguration.active) {
    // make module public to be available for callback after load
    window.StoreFinder = new FrontendMap(window.mapConfiguration, window.locations);
  }
});

// return constructor
export = FrontendMap;
