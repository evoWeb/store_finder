/* global window, define, jQuery */
(function (factory) {
  'function' === typeof define && define.amd ? define('map', ['jquery', 'window'], factory) : factory(jQuery, window)
})(function ($, root) {
  'use strict';

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
    this.infoWindow = new google.maps.InfoWindow();
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
      this.infoWindow.close();
      this.infoWindow.setContent(this.infoWindowTemplate.render(location.information));
      this.infoWindow.setPosition(marker.getPosition());
      this.infoWindow.open(this.map, marker);
    }
  };

  /**
   * Trigger click event on marker on click in result list
   *
   * @param {number} index
   */
  StoreFinderMap.prototype.openInfoWindow = function (index) {
    google.maps.event.trigger(this.locations[index].marker, 'click');
  };

  /**
   * Initialize location marker on map
   */
  StoreFinderMap.prototype.initializeLocation = function () {
    var self = this;
    self.locations.map(function (location, index) {
      location['information']['index'] = index;

      var icon,
        markerArguments = {
          map: self.map,
          title: location.name,
          position: new google.maps.LatLng(location.lat, location.lng)
        };

      if (location.information.icon) {
        icon = location.information.icon;
      } else if (self.mapConfiguration.hasOwnProperty('markerIcon')) {
        icon = self.mapConfiguration.markerIcon;
      }

      if (icon) {
        markerArguments.icon = icon;
      }

      var marker = new google.maps.Marker(markerArguments);
      marker.sfLocation = location;

      google.maps.event.addListener(marker, 'click', function () {
        self.showInformation(this);
      });

      // attach marker to location to be able to close it later
      location.marker = marker;
    });
  };

  /**
   * Initialize map
   */
  StoreFinderMap.prototype.initializeMap = function () {
    var self = this, center;

    google.maps.visualRefresh = true;

    if (typeof self.mapConfiguration.center !== 'undefined') {
      center = new google.maps.LatLng(self.mapConfiguration.center.lat, self.mapConfiguration.center.lng);
    } else {
      center = new google.maps.LatLng(0, 0);
    }

    var mapOptions = {
      zoom: parseInt(self.mapConfiguration.zoom, 10),
      center: center,
      disableDefaultUI: true, // a way to quickly hide all controls
      zoomControl: true,
      zoomControlOptions: {
        style: google.maps.ZoomControlStyle.LARGE
      }
    };

    self.map = new google.maps.Map($('#tx_storefinder_map')[0], mapOptions);

    if (self.mapConfiguration.afterSearch === 0 && navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(function (position) {
        var pos = {
          lat: position.coords.latitude,
          lng: position.coords.longitude
        };

        self.map.setCenter(pos);
      });
    }
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
   * Load google map script
   */
  StoreFinderMap.prototype.loadScript = function () {
    var self = this,
      apiUrl = 'https://maps.googleapis.com/maps/api/js?v=3.exp',
      parameter = '&key=' + self.mapConfiguration.apiConsoleKey
        + '&sensor=' + (self.mapConfiguration.allowSensors ? 'true' : 'false');

    if (self.mapConfiguration.language !== '') {
      parameter += '&language=' + self.mapConfiguration.language;
    }

    if (self.mapConfiguration.hasOwnProperty('apiUrl')) {
      apiUrl = self.mapConfiguration.apiUrl;
    }

    $.when(
      $.getScript(apiUrl + parameter)
    ).done(function () {
      var interval = setInterval(function () {
        if (typeof root.google !== 'undefined') {
          root.clearInterval(interval);
          self.postLoadScript();
        }
      }, 10);
    }).fail(function () {
      console.log('Failed loading google maps resources.');
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
