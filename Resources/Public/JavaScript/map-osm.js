/* global window, define, jQuery */
(function(factory) {
	'function' === typeof define && define.amd ? define('map', ['jquery', 'window'], factory) : factory(jQuery, window)
})(function($, root) {
	var module = {};

	var L,
		map,
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

		infoWindow = L.popup();
	};

	/**
	 * Close previously open info window, renders new content and opens the window
	 */
	module.showInformation = function() {
		'use strict';

		var marker = this,
			location = marker.sfLocation;

		if (typeof mapConfiguration.insertSingleViewInto !== 'undefined') {
			alert('Using configuration.insertSingleViewInto is deprecated please use configuration.renderSingleViewCallback instead');
		} else if (typeof mapConfiguration.renderSingleViewCallback === 'function') {
			mapConfiguration.renderSingleViewCallback(location, infoWindowTemplate);
		} else {
			if (infoWindow.isOpen()) {
				infoWindow.closePopup();
			}

			infoWindow = marker.getPopup()
				.setLatLng(L.latLng(location.lat, location.lng))
				.setContent(infoWindowTemplate.render(location.information))
				.openOn(map);
		}
	};

	/**
	 * Trigger click event on marker on click in result list
	 *
	 * @param {Integer} index
	 */
	module.openInfoWindow = function(index) {
		'use strict';

		locations[index].marker.click();
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

				var marker = L.marker([location.lat, location.lng], {
					title: location.name
				}).bindPopup('').addTo(map);

				marker.sfLocation = location;

				marker.on('click', module.showInformation);

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

		L = root.L;
		map = L.map('tx_storefinder_map');

		if (typeof mapConfiguration.center !== 'undefined') {
			map.setView(
				[mapConfiguration.center.lat, mapConfiguration.center.lng],
				parseInt(mapConfiguration.zoom, 10)
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
		).addTo(map);
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
		//module.initializeLayer();
		module.initializeLocation();
		module.initializeInfoWindow();
		module.initializeTemplates();
		module.initializeListEvents();
	};

	/**
	 * Load open street map leaflet script
	 */
	module.loadScript = function() {
		'use strict';

		var $css = $.Deferred();
		var $cssFile = $('<link/>', {
			rel: 'stylesheet',
			type: 'text/css',
			href: 'https://unpkg.com/leaflet@1.3.1/dist/leaflet.css',
			integrity: 'sha512-Rksm5RenBEKSKFjgI3a41vrjkw4EVPlJ3+OiI65vTjIdo9brlAacEuKOiQ5OFh7cOI1bkDwLqdLw3Zg0cRJAAQ==',
			crossorigin: ''
		}).appendTo('head');
		$css.resolve($cssFile);

		var $js = $.Deferred();
		var $jsFile = $('<script/>', {
			src: 'https://unpkg.com/leaflet@1.3.1/dist/leaflet.js',
			integrity: 'sha512-/Nsx9X4HebavoBvEBuyp3I7od5tA0UzAxs+j83KgC8PU0kgB4XiK4Lfe4y4cgBtaRJQEIFCW+oC506aPT2L1zw==',
			crossorigin: ''
		}).appendTo('head');
		$js.resolve($jsFile);

		$.when($css.promise(), $js.promise()).then(
			function() {
				var interval = setInterval(function() {
					if (typeof root.L !== 'undefined') {
						root.clearInterval(interval);
						module.postLoadScript();
					}
				}, 10);
			},
			function () {
				console.log('Failed loading osm resources.');
			}
		);
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
