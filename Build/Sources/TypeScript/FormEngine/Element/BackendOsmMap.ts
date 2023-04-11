/**
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

/// <reference types="../../../types/index"/>

import * as $ from 'jquery';
/* eslint-disable */
// @ts-ignore
import * as L from 'TYPO3/CMS/StoreFinder/Vendor/Leaflet/leaflet';
/* eslint-enable */

export default class BackendOsmMap {
  private map: L.Map;
  private marker: L.Marker;
  private mapConfiguration: BackendConfiguration = {
    uid: '0',
    latitude: 0,
    longitude: 0,
    zoom: 15
  };

  constructor(options: BackendConfiguration) {
    this.mapConfiguration = options;

    this.initializeMap();
    this.initializeMarker();
    this.initializeEvents();
    setTimeout(() => { this.map.invalidateSize(); }, 10);
  }

  initializeMap(this: BackendOsmMap): void {
    this.map = L.map('map');
    this.map.setView(
      [this.mapConfiguration.latitude, this.mapConfiguration.longitude],
      this.mapConfiguration.zoom
    );
    this.map.doubleClickZoom.disable();

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(this.map);
  }

  initializeMarker(this: BackendOsmMap): void {
    const options = {
      draggable: true
    };
    this.marker = new L.Marker([this.mapConfiguration.latitude, this.mapConfiguration.longitude], options);
    this.marker.bindPopup('').addTo(this.map);
  }

  initializeEvents(this: BackendOsmMap): void {
    $('.t3js-tabmenu-item a').on('click', (event: JQuery.ClickEvent) => {
      $('#' + $(event.target).attr('aria-controls')).trigger('cssActiveAdded');
    });

    $('#map').parents('.tab-pane').on('cssActiveAdded', () => {
      setTimeout(() => { this.map.invalidateSize(); }, 10);
    });

    this.map.on('dblclick', (event: L.LeafletEvent) => {
      const coordinates = event.latlng;
      this.marker.setLatLng(coordinates);
      this.updateCoordinateFields(coordinates, this);
      return false;
    });

    this.marker.on('moveend', (event: L.LeafletEvent) => {
      const coordinates = event.target.getLatLng();
      this.updateCoordinateFields(coordinates, this);
    });
  }

  updateCoordinateFields(coordinates: L.LatLng, backend: BackendOsmMap): void {
    const fieldPrefix = 'data[tx_storefinder_domain_model_location][' + backend.mapConfiguration.uid + ']',
      $latitudeField = $('*[data-formengine-input-name="' + fieldPrefix + '[latitude]"]'),
      $longitudeField = $('*[data-formengine-input-name="' + fieldPrefix + '[longitude]"]');

    $latitudeField.val(coordinates.lat).trigger('change');
    $longitudeField.val(coordinates.lng).trigger('change');
  }
}
