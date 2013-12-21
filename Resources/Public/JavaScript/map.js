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

function initializeTemplates() {
	'use strict';

	var source = $('#templateInfoWindow').html();
	infoWindowTemplate = Hogan.compile(source);
}

function initializeInfoWindow() {
	'use strict';

	infoWindow = new google.maps.InfoWindow();
	// infoWindow = new InfoBubble();
}

function showInformations(event) {
	var marker = this,
		location = this.sfLocation;

	var html = infoWindowTemplate.render(location.information);

	infoWindow.close();
	infoWindow.setContent(html);
	infoWindow.setPosition(marker.getPosition());
	infoWindow.open(map, marker);
}

/**
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
	map = new google.maps.Map($('#tx_storefinder_pi1_map')[0], mapOptions);

	initializeLayer();
	initializeLocation();
	initializeInfoWindow();
	initializeTemplates();
}

/**
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