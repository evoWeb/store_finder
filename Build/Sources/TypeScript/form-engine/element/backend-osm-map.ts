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

import * as L from '@evoweb/store-finder/leaflet/leaflet-src.esm.js';

export default class BackendOsmMap {
  private mapConfiguration: BackendConfiguration = {
    uid: '0',
    mapId: '',
    latitude: 0,
    longitude: 0,
    zoom: 16
  };
  private map: L.Map;
  private marker: L.Marker;
  private latitudeField: HTMLInputElement;
  private longitudeField: HTMLInputElement;

  public constructor(mapConfiguration: BackendConfiguration) {
    this.mapConfiguration = mapConfiguration;

    this.initializeFields();
    this.initializeMap();
    this.initializeMarker();
    this.initializeEvents();

    this.resizeMap();
  }

  private initializeFields(): void {
    const fieldPrefix = 'data[tx_storefinder_domain_model_location][' + this.mapConfiguration.uid + ']';

    this.latitudeField = document.querySelector('[data-formengine-input-name="' + fieldPrefix + '[latitude]"]');
    this.longitudeField = document.querySelector('[data-formengine-input-name="' + fieldPrefix + '[longitude]"]');
  }

  private initializeMap(): void {
    this.map = L.map(this.mapConfiguration.mapId);
    this.map.setView(
      new L.LatLng(this.mapConfiguration.latitude, this.mapConfiguration.longitude),
      this.mapConfiguration.zoom
    );
    this.map.doubleClickZoom.disable();

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
      minZoom: 8
    }).addTo(this.map);
  }

  private initializeMarker(): void {
    const options = {
      draggable: true
    };
    this.marker = new L.Marker(new L.LatLng(this.mapConfiguration.latitude, this.mapConfiguration.longitude), options);
    this.marker.bindPopup('').addTo(this.map);
  }

  private initializeEvents(): void {
    let mapParentTab = document.querySelector('#' + this.mapConfiguration.mapId) as HTMLElement;
    while ((mapParentTab = mapParentTab.parentElement) && mapParentTab.tagName != 'body') {
      if (mapParentTab.matches('.tab-pane')) {
        break;
      }
    }

    [...document.querySelectorAll('.t3js-tabmenu-item a')].forEach(tab => {
      tab.addEventListener('click', (event: MouseEvent) => {
        if (mapParentTab.id === (event.target as HTMLAnchorElement).getAttribute('aria-controls')) {
          this.resizeMap();
        }
      })
    });

    this.map.on('dblclick', (event: L.LeafletMouseEvent) => {
      const coordinates = event.latlng;
      this.marker.setLatLng(coordinates);
      this.updateCoordinateFields(coordinates);
    });

    this.marker.on('moveend', (event: L.LeafletEvent) => {
      const coordinates = event.target.getLatLng();
      this.updateCoordinateFields(coordinates);
    });
  }

  private updateCoordinateFields(coordinates: L.LatLng): void {
    this.latitudeField.value = coordinates.lat.toString();
    this.latitudeField.dispatchEvent(new Event('change', { bubbles: true }));

    this.longitudeField.value = coordinates.lng.toString();
    this.longitudeField.dispatchEvent(new Event('change', { bubbles: true }));
  }

  private resizeMap(): void {
    setTimeout(() => { this.map.invalidateSize(); }, 10);
  }
}
