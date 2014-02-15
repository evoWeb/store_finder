var map,
	mapConfiguration = mapConfiguration || {
		active: false,
		apiV3Layers: '',
		language: '',
		allowSensore: false
	},
	locations = locations || [],
	infoWindow,
	infoWindowTemplate;

/**
 * Initialize information layer on map
 *
 * @return void
 */
function initializeLayer() {
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
}

/**
 * Initialize info window template
 *
 * @return void
 */
function initializeTemplates() {
	'use strict';

	var source = $('#templateInfoWindow').html();
	infoWindowTemplate = Hogan.compile(source);
}

/**
 * Initialize instance of map infoWindow
 *
 * @return void
 */
function initializeInfoWindow() {
	'use strict';

	infoWindow = new google.maps.InfoWindow();
}

/**
 * Close previously open info window, renders new content and opens the window
 *
 * @return void
 */
function showInformations() {
	'use strict';

	var marker = this,
		location = this.sfLocation,
		html = infoWindowTemplate.render(location.information);

	infoWindow.close();
	infoWindow.setContent(html);
	infoWindow.setPosition(marker.getPosition());
	infoWindow.open(map, marker);
}

/**
 * Trigger click event on marker on click in result list
 *
 * @param index
 * @return void
 */
function openInfoWindow(index) {
	'use strict';

	google.maps.event.trigger(locations[index].marker, 'click');
}

/**
 * Initialize location marker on map
 *
 * @return void
 */
function initializeLocation() {
	'use strict';

	var index, location;
	for (index = 0; index < locations.length; index++) {
		location = locations[index];

		location.marker = new google.maps.Marker({
			map: map,
			title: location.name,
			position: new google.maps.LatLng(location.lat, location.lng)
		});
		location.marker.sfLocation = location;

		google.maps.event.addListener(location.marker, 'click', showInformations);
	}
}

/**
 * Initialize map
 *
 * @return void
 */
function initializeMap() {
	'use strict';

	google.maps.visualRefresh = true;

	var mapOptions = {
		zoom: parseInt(mapConfiguration.zoom, 10),
		center: new google.maps.LatLng(mapConfiguration.center.lat, mapConfiguration.center.lng),
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};
	map = new google.maps.Map($('#tx_storefinder_map')[0], mapOptions);

	initializeLayer();
	initializeLocation();
	initializeInfoWindow();
	initializeTemplates();
}

/**
 * Load google map script
 *
 * @return void
 */
function loadScript() {
	'use strict';

	var parameter = '&callback=initializeMap';

	parameter += '&sensor=' + (mapConfiguration.allowSensore ? 'true' : 'false');

	if (mapConfiguration.language !== '') {
		parameter += '&language=' + mapConfiguration.language;
	}

	$.getScript('https://maps.googleapis.com/maps/api/js?v=3.exp' + parameter);
}

$(window).on('load', function() {
	'use strict';

	if (mapConfiguration.active) {
		loadScript();
	}
});