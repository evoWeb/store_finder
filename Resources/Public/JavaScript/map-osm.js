/* global window, define, jQuery */
(function (factory) {
  'function' === typeof define && define.amd ? define('map', ['jquery', 'window'], factory) : factory(jQuery, window)
})(function ($, root) {
  'use strict'; var L = root.L;

  function StoreFinderMap(mapConfiguration, locations) {
    this.map = null;
    this.mapConfiguration = mapConfiguration || {
      active: false,
      afterSearch: 0,
      apiConsoleKey: '',
      apiUrl: '',
      apiV3Layers: '',
      kmlUrl: '',
      language: 'en',
      allowSensors: false,
      renderSingleViewCallback: null,
      handleCloseButtonCallback: null
    };
    this.locations = locations || [];
    this.infoWindow = null;
    this.infoWindowTemplate = null;

    this.loadScript();
  }

  /**
   * Initialize information layer on map
   */
  StoreFinderMap.prototype.initializeLayer = function () {
    if (this.mapConfiguration.apiV3Layers.indexOf('traffic') > -1) {
      var trafficLayer = new google.maps.TrafficLayer();
      trafficLayer.setMap(this.map);
    }

    if (this.mapConfiguration.apiV3Layers.indexOf('bicycling') > -1) {
      var bicyclingLayer = new google.maps.BicyclingLayer();
      bicyclingLayer.setMap(this.map);
    }

    if (this.mapConfiguration.apiV3Layers.indexOf('panoramio') > -1) {
      var panoramioLayer = new google.maps.panoramio.PanoramioLayer();
      panoramioLayer.setMap(this.map);
    }

    if (this.mapConfiguration.apiV3Layers.indexOf('weather') > -1) {
      var weatherLayer = new google.maps.weather.WeatherLayer({
        temperatureUnits: google.maps.weather.TemperatureUnit.DEGREE
      });
      weatherLayer.setMap(this.map);
    }

    if (this.mapConfiguration.apiV3Layers.indexOf('kml') > -1) {
      var kmlLayer = new google.maps.KmlLayer(this.mapConfiguration.kmlUrl);
      kmlLayer.setMap(this.map);
    }
  };

  /**
   * Initialize info window template
   */
  StoreFinderMap.prototype.initializeTemplates = function () {
    var self = this;
    self.infoWindowTemplate = Hogan.compile($('#templateInfoWindow').html());

    $(document).on('click', '.tx-storefinder .infoWindow .close', function () {
      var $closeButton = $(this);

      if (typeof self.mapConfiguration.renderSingleViewCallback === 'function') {
        self.mapConfiguration.handleCloseButtonCallback($closeButton);
      } else {
        self.infoWindow.close();
      }
    });
  };

  /**
   * Initialize instance of map infoWindow
   */
  StoreFinderMap.prototype.initializeInfoWindow = function () {
    this.infoWindow = L.popup();
  };

  /**
   * Close previously open info window, renders new content and opens the window
   *
   * @param {object} marker
   */
  StoreFinderMap.prototype.showInformation = function (marker) {
    var location = marker.sfLocation;

    if (typeof this.mapConfiguration.renderSingleViewCallback === 'function') {
      this.mapConfiguration.renderSingleViewCallback(location, this.infoWindowTemplate);
    } else {
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
   * Trigger click event on marker on click in result list
   *
   * @param {number} index
   */
  StoreFinderMap.prototype.openInfoWindow = function (index) {
    this.locations[index].marker.click();
  };

  /**
   * Initialize location marker on map
   */
  StoreFinderMap.prototype.initializeLocation = function () {
    var self = this;
    self.locations.map(function (location, index) {
      location['information']['index'] = index;

      var marker = L.marker([location.lat, location.lng], {
        title: location.name
      }).bindPopup('').addTo(self.map);

      marker.sfLocation = location;

      marker.on('click', function () { self.showInformation(this); });

      // attach marker to location to be able to close it later
      location.marker = marker;
    });
  };

  /**
   * Initialize map
   */
  StoreFinderMap.prototype.initializeMap = function () {
    var self = this;

    self.map = L.map('tx_storefinder_map');

    if (typeof mapConfiguration.center !== 'undefined') {
      map.setView(
        [self.mapConfiguration.center.lat, self.mapConfiguration.center.lng],
        parseInt(self.mapConfiguration.zoom, 10)
      );
    } else {
      map.setView([0, 0], 13);
    }

    // more providers can be found here http://leaflet-extras.github.io/leaflet-providers/preview/
    L.tileLayer(
      'https://korona.geog.uni-heidelberg.de/tiles/roads/x={x}&y={y}&z={z}', {
        maxZoom: 20,
        attribution: 'Imagery from <a href="http://giscience.uni-hd.de/">GIScience Research Group @ University of Heidelberg</a> &mdash; Map data &copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
      }
    ).addTo(self.map);
  };

  /**
   * Initialize list click events
   */
  StoreFinderMap.prototype.initializeListEvents = function () {
    var self = this;
    $(document).on('click', '.tx-storefinder .resultList > li', function () {
      self.openInfoWindow($(this).data('index'));
    });
  };

  /**
   * Initialize map
   */
  StoreFinderMap.prototype.postLoadScript = function () {
    this.initializeMap();
    this.initializeLayer();
    this.initializeLocation();
    this.initializeInfoWindow();
    this.initializeTemplates();
    this.initializeListEvents();
  };

  /**
   * Load open street map leaflet script
   */
  StoreFinderMap.prototype.loadScript = function () {
    var self = this;
    var $css = $.Deferred(),
      $cssFile = $('<link/>', {
        rel: 'stylesheet',
        type: 'text/css',
        href: 'https://unpkg.com/leaflet@1.3.1/dist/leaflet.css',
        integrity: 'sha512-Rksm5RenBEKSKFjgI3a41vrjkw4EVPlJ3+OiI65vTjIdo9brlAacEuKOiQ5OFh7cOI1bkDwLqdLw3Zg0cRJAAQ==',
        crossorigin: ''
      }).appendTo('head');
    $css.resolve($cssFile);

    var $js = $.Deferred(),
      $jsFile = $('<script/>', {
        src: 'https://unpkg.com/leaflet@1.3.1/dist/leaflet.js',
        integrity: 'sha512-/Nsx9X4HebavoBvEBuyp3I7od5tA0UzAxs+j83KgC8PU0kgB4XiK4Lfe4y4cgBtaRJQEIFCW+oC506aPT2L1zw==',
        crossorigin: ''
      }).appendTo('head');
    $js.resolve($jsFile);

    $.when(
      $css.promise(),
      $js.promise()
    ).done(function () {
      var interval = setInterval(function () {
        if (typeof root.L !== 'undefined') {
          root.clearInterval(interval);
          self.postLoadScript();
        }
      }, 10);
    }).fail(function () {
        console.log('Failed loading osm resources.');
    });
  };

  $(document).ready(function () {
    if (root.mapConfiguration.active) {
      // make module public to be available for callback after load
      root.StoreFinder = new StoreFinderMap(root.mapConfiguration, root.locations);
    }
  });

  return StoreFinderMap;
});
