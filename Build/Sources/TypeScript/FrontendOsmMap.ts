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

import FrontendMap from './FrontendMap';
import * as L from 'leaflet';

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
  initializeMap(): void {
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
  initializeLayer(): void {
    if (this.mapConfiguration.apiV3Layers.indexOf('kml') > -1) {
      Promise.all([
        this.createFilePromise(
          'https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-omnivore/v0.3.1/leaflet-omnivore.min.js'
        )
      ])
        .then(() => {
          const kmlLayer = omnivore.kml(this.mapConfiguration.kmlUrl);
          kmlLayer.setMap(this.map);
        })
        .catch(() => {
          console.log('Failed loading resources.');
        });
    }
  }

  /**
   * Close previously open info window, renders new content and opens the window
   */
  showInformation(location: Location, marker: L.Marker): void {
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
  initializeInfoWindow(): void {
    this.infoWindow = L.popup();
  }

  /**
   * Close info window
   */
  closeInfoWindow(): void {
    this.infoWindow.closePopup();
  }

  /**
   * Trigger click event on marker on click in result list
   */
  openInfoWindow(index: number): void {
    this.locations[index].marker.fire('click');
  }

  /**
   * Load open street map leaflet script
   */
  loadScript(): void {
    Promise.all([
      this.createFilePromise(
        'https://unpkg.com/leaflet@1.9.3/dist/leaflet.css',
        'sha512-mD70nAW2ThLsWH0zif8JPbfraZ8hbCtjQ+5RU1m4+ztZq6/MymyZeB55pWsi4YAX+73yvcaJyk61mzfYMvtm9w==',
        'anonymous'
      ),
      this.createFilePromise(
        'https://unpkg.com/leaflet@1.9.3/dist/leaflet.js',
        'sha512-Dqm3h1Y4qiHUjbhxTuBGQsza0Tfppn53SHlu/uj1f+RT+xfShfe7r6czRf5r2NmllO2aKx+tYJgoxboOkn1Scg==',
        'anonymous'
      )
    ])
      .then(() => {
        const wait = () => {
          if (typeof L !== 'undefined') {
            this.postLoadScript();
          } else {
            window.requestAnimationFrame(wait);
          }
        }
        window.requestAnimationFrame(wait);
      })
      .catch(() => {
        console.log('Failed loading resources.');
      });
  }
}

// return instance
new FrontendOsmMap();
