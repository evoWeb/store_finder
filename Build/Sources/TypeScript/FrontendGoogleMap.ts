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

/**
 * Module: Evoweb/StoreFinder/FrontendGoogleMap
 * contains all logic for the frontend map output
 */
class FrontendGoogleMap extends FrontendMap {
  private map: google.maps.Map;
  private infoWindow: google.maps.InfoWindow;

  /**
   * Initialize map
   */
  initializeMap(): void {
    let center;

    if (typeof this.mapConfiguration.center !== 'undefined') {
      center = new google.maps.LatLng(this.mapConfiguration.center.lat, this.mapConfiguration.center.lng);
    } else {
      center = new google.maps.LatLng(0, 0);
    }

    const mapOptions: google.maps.MapOptions = {
      zoom: this.mapConfiguration.zoom,
      center: center,
      disableDefaultUI: true, // a way to quickly hide all controls
      zoomControl: true,
      styles: ([] as google.maps.MapTypeStyle[]),
      zoomControlOptions: {
        position: google.maps.ControlPosition.RIGHT_BOTTOM,
      },
    };

    if (self.mapConfiguration.mapStyles) {
      mapOptions.styles = self.mapConfiguration.mapStyles;
    }

    this.map = new google.maps.Map(document.getElementById('tx_storefinder_map'), mapOptions);

    if (this.mapConfiguration.afterSearch === 0 && navigator.geolocation) {
      navigator.geolocation.getCurrentPosition((position) => {
        const pos = {
          lat: position.coords.latitude,
          lng: position.coords.longitude
        };

        this.map.setCenter(pos);
      });
    }
  }

  /**
   * Initialize information layer on map
   */
  initializeLayer(): void {
    if (this.mapConfiguration.apiV3Layers.indexOf('traffic') > -1) {
      const trafficLayer = new google.maps.TrafficLayer();
      trafficLayer.setMap(this.map);
    }

    if (this.mapConfiguration.apiV3Layers.indexOf('bicycling') > -1) {
      const bicyclingLayer = new google.maps.BicyclingLayer();
      bicyclingLayer.setMap(this.map);
    }

    if (this.mapConfiguration.apiV3Layers.indexOf('kml') > -1) {
      const kmlLayer = new google.maps.KmlLayer({ url : this.mapConfiguration.kmlUrl });
      kmlLayer.setMap(this.map);
    }
  }

  /**
   * Close previously open info window, renders new content and opens the window
   */
  showInformation(location: Location, marker: google.maps.Marker): void {
    if (typeof this.mapConfiguration.renderSingleViewCallback === 'function') {
      this.mapConfiguration.renderSingleViewCallback(location, this.infoWindowTemplate);
    } else {
      this.infoWindow.close();
      this.infoWindow.setContent(this.renderInfoWindowContent(location));
      this.infoWindow.setPosition(marker.getPosition());
      this.infoWindow.open(this.map, marker);
    }
  }

  /**
   * Create marker and add to map
   */
  createMarker(location: Location, icon: string): google.maps.Marker {
    const options = {
        title: location.name,
        position: new google.maps.LatLng(location.lat, location.lng),
        icon: icon,
      },
      marker = new google.maps.Marker(options);
    marker.setMap(this.map);

    marker.addListener('click', () => {
      this.showInformation(location, marker);
    });

    return marker;
  }

  /**
   * Initialize instance of map infoWindow
   */
  initializeInfoWindow(): void {
    this.infoWindow = new google.maps.InfoWindow();
  }

  /**
   * Close info window
   */
  closeInfoWindow() {
    this.infoWindow.close();
  }

  /**
   * Trigger click event on marker on click in result list
   */
  openInfoWindow(index: number): void {
    google.maps.event.trigger(this.locations[index].marker, 'click');
  }

  /**
   * Load google map script
   */
  loadScript(): void {
    let apiUrl = 'https://maps.googleapis.com/maps/api/js?v=3.exp',
      parameter = '&key=' + this.mapConfiguration.apiConsoleKey;

    if (this.mapConfiguration.language !== '') {
      parameter += '&language=' + this.mapConfiguration.language;
    }

    if (Object.prototype.hasOwnProperty.call(this.mapConfiguration, 'apiUrl')) {
      apiUrl = this.mapConfiguration.apiUrl;
    }

    Promise.all([
      this.createFilePromise(apiUrl + parameter)
    ])
      .then(() => {
        const wait = () => {
          if (typeof google !== 'undefined') {
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
new FrontendGoogleMap();
