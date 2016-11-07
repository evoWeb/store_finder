/* global window, define, jQuery */
(function(factory) {
	"function" == typeof define && define.amd ? define('map', ['jquery', 'window'], factory) : factory(jQuery, window)
})(function($, root) {
	var module = {};

	var map,
		mapConfiguration = root.mapConfiguration || {
				active: false,
				apiV3Layers: '',
				language: 'en',
				allowSensors: false
			},
		locations = root.locations || [],
		infoWindow,
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
	 *
	 * @return void
	 */
	module.initializeTemplates = function() {
		'use strict';

		var source = $('#templateInfoWindow').html();
		infoWindowTemplate = Hogan.compile(source);

		$(document).on('click', '.tx-storefinder .infoWindow .close', function() {
			var $closeButton = $(this);

			if (typeof mapConfiguration.insertSingleViewInto != 'undefined') {
				var $singleView = $closeButton.parents(mapConfiguration.insertSingleViewInto);
				$singleView.hide();
				$singleView.removeClass('show');
			} else {
				infoWindow.close();
			}
		});
	};

	/**
	 * Initialize instance of map infoWindow
	 *
	 * @return void
	 */
	module.initializeInfoWindow = function() {
		'use strict';

		infoWindow = new google.maps.InfoWindow();
	};

	/**
	 * Close previously open info window, renders new content and opens the window
	 *
	 * @return void
	 */
	module.showInformations = function() {
		'use strict';

		var marker = this,
			location = this.sfLocation;

		location.information.staticMapCenter = encodeURIComponent(location.information.address) + ',+'
			+ encodeURIComponent(location.information.zipcode) + ',+'
			+ encodeURIComponent(location.information.city) + ',+'
			+ encodeURIComponent(location.information.country);

		var html = infoWindowTemplate.render(location.information);

		if (typeof mapConfiguration.insertSingleViewInto != 'undefined') {
			var $singleView = $(mapConfiguration.insertSingleViewInto);
			if ($singleView.hasClass('show')) {
				$singleView.hide();
				$singleView.removeClass('show');
			}
			$singleView.html(html);
			$singleView.show();
			$singleView.addClass('show');

			$("body").trigger("initializeTabs");
		} else {
			infoWindow.close();
			infoWindow.setContent(html);
			infoWindow.setPosition(marker.getPosition());
			infoWindow.open(map, marker);
		}
	};

	/**
	 * Trigger click event on marker on click in result list
	 *
	 * @param index
	 * @return void
	 */
	module.openInfoWindow = function(index) {
		'use strict';

		google.maps.event.trigger(locations[index].marker, 'click');
	};

	/**
	 * Initialize location marker on map
	 *
	 * @return void
	 */
	module.initializeLocation = function() {
		'use strict';

		var index, location;
		if (locations.length) {
			for (index = 0; index < locations.length; index++) {
				location = locations[index];
				location.information.index = index;

				var marker = new google.maps.Marker({
					map: map,
					title: location.name,
					position: new google.maps.LatLng(location.lat, location.lng)
				});
				marker.sfLocation = location;

				// attach marker to location to be able to close it later
				location.marker = marker;

				google.maps.event.addListener(marker, 'click', module.showInformations);
			}
		}
	};

	/**
	 * Initialize map
	 *
	 * @return void
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

		if (mapConfiguration.afterSearch == 0 && navigator.geolocation) {
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
	 *
	 * @return void
	 */
	module.initializeListEvents = function() {
		'use strict';

		$(document).on('click', '.tx-storefinder .resultList > li', function() {
			module.openInfoWindow($(this).data('index'));
		});
	};

	/**
	 * Initialize map
	 *
	 * @return void
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
	 *
	 * @param {object} configuration
	 *
	 * @return void
	 */
	module.loadScript = function(configuration) {
		'use strict';

		// make module public to be available for callback after load
		root.StoreFinder = module;

		var parameter = '&key=' + configuration.apiConsoleKey;
		parameter += '&callback=StoreFinder.postLoadScript';
		parameter += '&sensor=' + (mapConfiguration.allowSensors ? 'true' : 'false');

		if (mapConfiguration.language !== '') {
			parameter += '&language=' + mapConfiguration.language;
		}

		$.getScript('https://maps.googleapis.com/maps/api/js?v=3.exp' + parameter);
	};

	$(document).ready(function() {
		'use strict';

		if (mapConfiguration.active) {
			module.loadScript(mapConfiguration);
		}
	});

	return module;
});