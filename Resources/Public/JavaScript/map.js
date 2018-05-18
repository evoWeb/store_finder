/* global window, define, jQuery */
(function(factory) {
	'function' === typeof define && define.amd ? define('map', ['jquery', 'window'], factory) : factory(jQuery, window)
})(function($, root) {
	var module = {};


	var map,
		mapConfiguration = root.mapConfiguration || {
			active: false,
			apiV3Layers: '',
			language: 'en',
			allowSensors: false,
			renderSingleViewCallback: null,
			handleCloseButtonCallback: null
		},
		locations = root.locations || [],
		infoWindow = null,
		infoWindowTemplate;

	/**
	 * Initialize information layer on map
	 *
	 * @return void
	 */
	module.initializeLayer = function() {
		'use strict';
		if (mapConfiguration.apiV3Layers.indexOf('traffic') > -1) {
			var trafficLayer = new google.maps.TrafficLayer();
			trafficLayer.setMap(map);
		}

		if (mapConfiguration.apiV3Layers.indexOf('bicycling') > -1) {
			var bicyclingLayer = new google.maps.BicyclingLayer();
			bicyclingLayer.setMap(map);
		}

		if (mapConfiguration.apiV3Layers.indexOf('panoramio') > -1) {
			var panoramioLayer = new google.maps.panoramio.PanoramioLayer();
			panoramioLayer.setMap(map);
		}

		if (mapConfiguration.apiV3Layers.indexOf('weather') > -1) {
			var weatherLayer = new google.maps.weather.WeatherLayer({
				temperatureUnits: google.maps.weather.TemperatureUnit.DEGREE
			});
			weatherLayer.setMap(map);
		}

		if (mapConfiguration.apiV3Layers.indexOf('kml') > -1) {
			var kmlLayer = new google.maps.KmlLayer(mapConfiguration.kmlUrl);
			kmlLayer.setMap(map);
		}
	};

	/**
	 * Initialize info window template
	 */
	module.initializeTemplates = function() {
		'use strict';

		var source = $('#templateInfoWindow').html();
		infoWindowTemplate = Hogan.compile(source);

		$(document).on('click', '.tx-storefinder .infoWindow .close', function() {
			var $closeButton = $(this);

			if (typeof mapConfiguration.insertSingleViewInto !== 'undefined') {
				alert('Using configuration.insertSingleViewInto is deprecated please use configuration.handleCloseButtonCallback instead');
			} else if (typeof configuration.renderSingleViewCallback === 'function') {
				configuration.handleCloseButtonCallback($closeButton);
			} else {
				infoWindow.close();
			}
		});
	};

	/**
	 * Initialize instance of map infoWindow
	 */
	module.initializeInfoWindow = function() {
		'use strict';

		infoWindow = new google.maps.InfoWindow();
	};

	/**
	 * Close previously open info window, renders new content and opens the window
	 */
	module.showInformation = function() {
		'use strict';

		var marker = this,
			location = this.sfLocation;

		if (typeof mapConfiguration.insertSingleViewInto !== 'undefined') {
			alert('Using configuration.insertSingleViewInto is deprecated please use configuration.renderSingleViewCallback instead');
		} else if (typeof mapConfiguration.renderSingleViewCallback === 'function') {
			mapConfiguration.renderSingleViewCallback(location, infoWindowTemplate);
		} else {
			infoWindow.close();
			infoWindow.setContent(infoWindowTemplate.render(location.information));
			infoWindow.setPosition(marker.getPosition());
			infoWindow.open(map, marker);
		}
	};

	/**
	 * Trigger click event on marker on click in result list
	 *
	 * @param {Integer} index
	 */
	module.openInfoWindow = function(index) {
		'use strict';

		google.maps.event.trigger(locations[index].marker, 'click');
	};

	/**
	 * Initialize location marker on map
	 */
	module.initializeLocation = function() {
		'use strict';

		var index, location;
		if (locations.length) {
			for (index = 0; index < locations.length; index++) {
				location = locations[index];
				location['information']['index'] = index;

				var marker = new google.maps.Marker({
					map: map,
					title: location.name,
					position: new google.maps.LatLng(location.lat, location.lng)
				});
				marker.sfLocation = location;

				google.maps.event.addListener(marker, 'click', module.showInformation);

				// attach marker to location to be able to close it later
				location.marker = marker;
			}
		}
	};

	/**
	 * Initialize map
	 */
	module.initializeMap = function() {
		'use strict';

		var center;

		google.maps.visualRefresh = true;

		if (typeof mapConfiguration.center !== 'undefined') {
			center = new google.maps.LatLng(mapConfiguration.center.lat, mapConfiguration.center.lng);
		} else {
			center = new google.maps.LatLng(0, 0);
		}

		var mapOptions = {
			zoom: parseInt(mapConfiguration.zoom, 10),
			center: center,
			disableDefaultUI: true, // a way to quickly hide all controls
			zoomControl: true,
			zoomControlOptions: {
				style: google.maps.ZoomControlStyle.LARGE
			}
		};

		map = new google.maps.Map($('#tx_storefinder_map')[0], mapOptions);

		if (mapConfiguration['afterSearch'] === 0 && navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(function (position) {
				var pos = {
					lat: position.coords.latitude,
					lng: position.coords.longitude
				};

				map.setCenter(pos);
			});
		}
	};

	/**
	 * Initialize list click events
	 */
	module.initializeListEvents = function() {
		'use strict';

		$(document).on('click', '.tx-storefinder .resultList > li', function() {
			module.openInfoWindow($(this).data('index'));
		});
	};

	/**
	 * Initialize map
	 */
	module.postLoadScript = function() {
		'use strict';

		module.initializeMap();
		module.initializeLayer();
		module.initializeLocation();
		module.initializeInfoWindow();
		module.initializeTemplates();
		module.initializeListEvents();
	};

	/**
	 * Load google map script
	 */
	module.loadScript = function() {
		'use strict';

		var parameter = '&key=' + mapConfiguration.apiConsoleKey;
		parameter += '&callback=StoreFinder.postLoadScript';
		parameter += '&sensor=' + (mapConfiguration.allowSensors ? 'true' : 'false');

		if (mapConfiguration.language !== '') {
			parameter += '&language=' + mapConfiguration.language;
		}

		$.getScript('https://maps.googleapis.com/maps/api/js?v=3.exp' + parameter);
	};

	$(document).ready(function() {
		'use strict';

		// make module public to be available for callback after load
		root.StoreFinder = module;

		if (mapConfiguration.active) {
			module.loadScript();
		}
	});

	return module;
});
