define(['jquery', 'TYPO3/CMS/StoreFinder/Leaflet'],
	function($, L) {
		'use strict';

		function LocationMap(options) {
			this.options = options || {};

			this.initializeMap();
			this.addMarker();
			this.triggerResizeOnActive();
		}

		LocationMap.prototype.initializeMap = function ()
		{
			this.map = L.map('map').setView([this.options.latitude, this.options.longitude], 15);

			L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
				attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
				maxZoom: 19
			}).addTo(this.map);
		};

		LocationMap.prototype.addMarker = function ()
		{
			var marker = L.marker([this.options.latitude, this.options.longitude], {draggable: true});
			marker.addTo(this.map);
			marker.on('moveend', this.updateCoordinateFields.bind(this))
		};

		LocationMap.prototype.triggerResizeOnActive = function ()
		{
			$('.t3js-tabmenu-item a').bind('click', function () {
				$('#' + $(this).attr('aria-controls')).trigger('cssActiveAdded');
			});

			$('#map').parents('.tab-pane').on('cssActiveAdded', function () {
				setTimeout(function () { this.invalidateSize(); }.bind(this.map), 10);
			}.bind(this));
		};

		LocationMap.prototype.updateCoordinateFields = function (event)
		{
			var movedMarker = event.target,
				coordinates = movedMarker.getLatLng(),
				fieldPrefix = 'data[tx_storefinder_domain_model_location][' + this.options.uid + ']',
				$latitudeField = $('*[data-formengine-input-name="' + fieldPrefix + '[latitude]"]'),
				$longitudeField = $('*[data-formengine-input-name="' + fieldPrefix + '[longitude]"]');

			$latitudeField.val(coordinates.lat).trigger('change');
			$longitudeField.val(coordinates.lng).trigger('change');
		};

		return LocationMap;
	});
