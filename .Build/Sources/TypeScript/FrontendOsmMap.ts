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

import * as L from 'leaflet';
import FrontendMap from './FrontendMap';

/**
 * Module: TYPO3/CMS/StoreFinder/FrontendOsmMap
 * contains all logic for the frontend map output
 * @exports TYPO3/CMS/StoreFinder/FrontendOsmMap
 */
class FrontendOsmMap extends FrontendMap {
  private map: L.Map;
  private infoWindow: L.Popup;

  /**
   * Initialize map
   */
  initializeMap(this: FrontendOsmMap) {
    this.map = L.map('tx_storefinder_map');

    if (typeof this.mapConfiguration.center !== 'undefined') {
      this.map.setView(
        [this.mapConfiguration.center.lat, this.mapConfiguration.center.lng],
        this.mapConfiguration.zoom
      );
    } else {
      this.map.setView([0, 0], 13);
    }

    // more providers can be found here http://leaflet-extras.github.io/leaflet-providers/preview/
    L.tileLayer(
      'https://korona.geog.uni-heidelberg.de/tiles/roads/x={x}&y={y}&z={z}', {
        maxZoom: 20,
        attribution: 'Imagery from <a href="https://www.geog.uni-heidelberg.de/gis/index_en.html">GIScience Research Group @ University of Heidelberg</a> &mdash; Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
      }
    ).addTo(this.map);
  }

  /**
   * Initialize information layer on map
   */
  initializeLayer(this: FrontendOsmMap) {
    if (this.mapConfiguration.apiV3Layers.indexOf('kml') > -1) {
      const $jsDeferred = $.Deferred(),
        $jsFile = $('<script/>', {
          src: 'https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-omnivore/v0.3.1/leaflet-omnivore.min.js',
          crossorigin: ''
        }).appendTo('head');

      $jsDeferred.resolve($jsFile);

      $.when($jsDeferred.promise()).done(() => {
        const kmlLayer = omnivore.kml(this.mapConfiguration.kmlUrl);
        kmlLayer.setMap(this.map);
      }).fail(() => {
        console.log('Failed loading resources.');
      });
    }
  }

  /**
   * Close previously open info window, renders new content and opens the window
   */
  showInformation(this: FrontendOsmMap, location: Location, marker: L.Marker) {
    if (typeof this.mapConfiguration.renderSingleViewCallback === 'function') {
      this.mapConfiguration.renderSingleViewCallback(location, this.infoWindowTemplate);
    } else {
      if (this.infoWindow.isOpen()) {
        this.infoWindow.closePopup();
      }
      this.infoWindow = marker.getPopup();
      this.infoWindow.setContent(this.renderInfoWindowContent(location));
      this.infoWindow.setLatLng(L.latLng(location.lat, location.lng));
      this.infoWindow.openOn(this.map);
    }
  }

  /**
   * Create marker and add to map
   */
  createMarker(location: Location, icon: string): L.Marker {
    const options = {
        title: location.name,
        icon: new L.Icon({ iconUrl: icon }),
      },
      marker = new L.Marker([location.lat, location.lng], options);
    marker.bindPopup('').addTo(this.map);

    marker.on('click', () => {
      this.showInformation(location, marker);
    });

    return marker;
  }

  /**
   * Initialize instance of map infoWindow
   */
  initializeInfoWindow(this: FrontendOsmMap) {
    this.infoWindow = L.popup();
  }

  /**
   * Close info window
   */
  closeInfoWindow() {
    this.infoWindow.closePopup();
  }

  /**
   * Trigger click event on marker on click in result list
   */
  openInfoWindow(this: FrontendMap, index: number) {
    this.locations[index].marker.fire('click');
  }

  /**
   * Load open street map leaflet script
   */
  loadScript() {
    const $cssDeferred = $.Deferred(),
      $cssFile = $('<link/>', {
        rel: 'stylesheet',
        type: 'text/css',
        href: 'https://unpkg.com/leaflet@1.5.1/dist/leaflet.css',
        integrity: 'sha512-puBpdR0798OZvTTbP4A8Ix/l+A4dHDD0DGqYW6RQ+9jxkRFclaxxQb/SJAWZfWAkuyeQUytO7+7N4QKrDh+drA==',
        crossorigin: ''
      }).appendTo('head'),
      $jsDeferred = $.Deferred(),
      $jsFile = $('<script/>', {
        src: 'https://unpkg.com/leaflet@1.5.1/dist/leaflet.js',
        integrity: 'sha512-nMMmRyTVoLYqjP9hrbed9S+FzjZHW5gY1TWCHA5ckwXZBadntCNs8kEqAWdrb9O7rxbCaA4lKTIWjDXZxflOcA==',
        crossorigin: ''
      }).appendTo('head');

    $cssDeferred.resolve($cssFile);
    $jsDeferred.resolve($jsFile);

    $.when(
      $cssDeferred.promise(),
      $jsDeferred.promise()
    ).done(() => {
      function wait(this: FrontendMap) {
        if (typeof L !== 'undefined') {
          this.postLoadScript();
        } else {
          window.requestAnimationFrame(wait.bind(this));
        }
      }
      window.requestAnimationFrame(wait.bind(this));
    }).fail(() => {
      console.log('Failed loading resources.');
    });
  }
}

// return instance
new FrontendOsmMap();
