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

/// <reference types="@types/googlemaps" />
import * as $ from 'jquery';

interface MapConfiguration {
  active: boolean,
  afterSearch: number;
  center?: {
    lat: number,
    lng: number
  };
  zoom?: string;

  apiConsoleKey: string,
  apiUrl: string,
  allowSensors: boolean,
  language: string,

  markerIcon: string,
  apiV3Layers: string,
  kmlUrl: string,
  renderSingleViewCallback(location: object, template: object): void,
  handleCloseButtonCallback(button: object): void,
}

interface Map {

}

interface Location {
  name: string,
  lat: number,
  lng: number,
  information: {
    index: number,
    icon: string,
  },
  marker: object
}

interface Marker {
  sfLocation: Location,
  getPosition(): Location,
}

interface InfoWindow {
  close(): void,
  open(map: object, marker: Marker): void,
  setContent(content: string): void,
  setPosition(location: Location): void
}

interface Template {
  render(information: object): string
}

declare global {
  interface Window {
    google: any;
    Hogan: any;
    mapConfiguration: MapConfiguration,
    locations: Array<Location>
    StoreFinder: object
  }
}

/**
 * Module: TYPO3/CMS/StoreFinder/FrontendMap
 * contains all logic for the frontend map output
 * @exports TYPO3/CMS/StoreFinder/FrontendMap
 */
class FrontendMap {
  private map: google.maps.Map;
  private mapConfiguration: MapConfiguration;
  private locations: Array<Location>;
  private locationIndex: number = 0;
  private infoWindow: InfoWindow;
  private infoWindowTemplate: Template;

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
  initializeMap = function (this: FrontendMap) {
    let center;

    window.google.maps.visualRefresh = true;

    if (typeof this.mapConfiguration.center !== 'undefined') {
      center = new window.google.maps.LatLng(this.mapConfiguration.center.lat, this.mapConfiguration.center.lng);
    } else {
      center = new window.google.maps.LatLng(0, 0);
    }

    let mapOptions = {
      zoom: parseInt(this.mapConfiguration.zoom, 10),
      center: center,
      disableDefaultUI: true, // a way to quickly hide all controls
      zoomControl: true,
      zoomControlOptions: {
        style: window.google.maps.ZoomControlStyle.LARGE
      }
    };

    this.map = new window.google.maps.Map($('#tx_storefinder_map')[0], mapOptions);

    if (this.mapConfiguration.afterSearch === 0 && navigator.geolocation) {
      navigator.geolocation.getCurrentPosition((position) => {
        let pos = {
          lat: position.coords.latitude,
          lng: position.coords.longitude
        };

        this.map.setCenter(pos);
      });
    }
  };

  /**
   * Initialize information layer on map
   */
  initializeLayer = function (this: FrontendMap) {
    if (this.mapConfiguration.apiV3Layers.indexOf('traffic') > -1) {
      let trafficLayer = new window.google.maps.TrafficLayer();
      trafficLayer.setMap(this.map);
    }

    if (this.mapConfiguration.apiV3Layers.indexOf('bicycling') > -1) {
      let bicyclingLayer = new window.google.maps.BicyclingLayer();
      bicyclingLayer.setMap(this.map);
    }

    if (this.mapConfiguration.apiV3Layers.indexOf('panoramio') > -1) {
      let panoramioLayer = new window.google.maps.panoramio.PanoramioLayer();
      panoramioLayer.setMap(this.map);
    }

    if (this.mapConfiguration.apiV3Layers.indexOf('weather') > -1) {
      let weatherLayer = new window.google.maps.weather.WeatherLayer({
        temperatureUnits: window.google.maps.weather.TemperatureUnit.DEGREE
      });
      weatherLayer.setMap(this.map);
    }

    if (this.mapConfiguration.apiV3Layers.indexOf('kml') > -1) {
      let kmlLayer = new window.google.maps.KmlLayer(this.mapConfiguration.kmlUrl);
      kmlLayer.setMap(this.map);
    }
  };

  /**
   * Close previously open info window, renders new content and opens the window
   */
  showInformation = function (this: FrontendMap, marker: Marker) {
    let location = marker.sfLocation;

    if (typeof this.mapConfiguration.renderSingleViewCallback === 'function') {
      this.mapConfiguration.renderSingleViewCallback(location, this.infoWindowTemplate);
    } else {
      this.infoWindow.close();
      this.infoWindow.setContent(this.infoWindowTemplate.render(location.information));
      this.infoWindow.setPosition(marker.getPosition());
      this.infoWindow.open(this.map, marker);
    }
  };

  /**
   * Process single location
   */
  processLocation = function (this: FrontendMap, location: Location) {
    let markerArguments = {
        title: location.name,
        position: new window.google.maps.LatLng(location.lat, location.lng),
        icon: '',
      };

    this.locationIndex++;
    location.information.index = this.locationIndex;

    if (location.information.icon) {
      markerArguments.icon = location.information.icon;
    } else if (this.mapConfiguration.hasOwnProperty('markerIcon')) {
      markerArguments.icon = this.mapConfiguration.markerIcon;
    }

    let marker = new window.google.maps.Marker(markerArguments);
    marker.sfLocation = location;
    marker.setMap(this.map);

    window.google.maps.event.addListener(marker, 'click', (event: Event, marker: Marker): void => {
      this.showInformation(marker);
    });

    // attach marker to location to be able to close it later
    location.marker = marker;
  };

  /**
   * Initialize location marker on map
   */
  initializeLocations = function (this: FrontendMap) {
    this.locations.map(this.processLocation.bind(this));
  };

  /**
   * Initialize instance of map infoWindow
   */
  initializeInfoWindow = function (this: FrontendMap) {
    this.infoWindow = new window.google.maps.InfoWindow();
  };

  /**
   * Initialize info window template
   */
  initializeTemplates = function (this: FrontendMap) {
    this.infoWindowTemplate = window.Hogan.compile($('#templateInfoWindow').html());

    $(document).on('click', '.tx-storefinder .infoWindow .close', (event: Event, $closeButton: JQuery): void => {
      if (typeof this.mapConfiguration.renderSingleViewCallback === 'function') {
        this.mapConfiguration.handleCloseButtonCallback($closeButton);
      } else {
        this.infoWindow.close();
      }
    });
  };

  /**
   * Trigger click event on marker on click in result list
   */
  openInfoWindow = function (this: FrontendMap, index: number) {
    window.google.maps.event.trigger(this.locations[index].marker, 'click');
  };

  /**
   * Initialize list click events
   */
  initializeListEvents = function (this: FrontendMap) {
    $(document).on('click', '.tx-storefinder .resultList > li', (event: Event, $field: JQuery): void => {
      this.openInfoWindow($field.data('index'));
    });
  };

  /**
   * Post load google map script processing
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
   * Load google map script
   */
  loadScript() {
    let self = this,
      apiUrl = 'https://maps.googleapis.com/maps/api/js?v=3.exp',
      parameter = '&key=' + this.mapConfiguration.apiConsoleKey
        + '&sensor=' + (this.mapConfiguration.allowSensors ? 'true' : 'false');

    if (self.mapConfiguration.language !== '') {
      parameter += '&language=' + self.mapConfiguration.language;
    }

    if (self.mapConfiguration.hasOwnProperty('apiUrl')) {
      apiUrl = self.mapConfiguration.apiUrl;
    }

    $.when(
      $.getScript(apiUrl + parameter)
    ).done(function () {
      function wait(this: FrontendMap) {
        if (typeof window.google !== 'undefined') {
          this.postLoadScript();
        } else {
          window.requestAnimationFrame(wait.bind(this));
        }
      }
      window.requestAnimationFrame(wait.bind(self));
    }).fail(function () {
      console.log('Failed loading google maps resources.');
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
