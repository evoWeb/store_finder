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

import * as Mustache from 'mustache';

export default class FrontendMap {
  public mapConfiguration: MapConfiguration;
  public locations: Array<Location> = [];
  public locationIndex: number = 0;
  public infoWindowTemplate: string = '';

  /**
   * The constructor, set the class properties default values
   */
  constructor() {
    if (typeof window.mapConfiguration == 'object' && window.mapConfiguration.active) {
      this.mapConfiguration = window.mapConfiguration;
    }
    if (this.mapConfiguration.active) {
      if (typeof window.locations == 'object') {
        this.locations = window.locations;
      }
      this.loadScript();
    }
  }

  initializeMap(): void {
    // do nothing.
  }

  initializeLayer(): void {
    // do nothing.
  }

  /**
   * Render content of the info window
   */
  renderInfoWindowContent(this: FrontendMap, location: Location): string {
    return Mustache.render(this.infoWindowTemplate, location.information)
  }

  /* eslint-disable */
  createMarker(location: Location, icon: string): void {
    // do nothing.
  }
  /* eslint-enable */

  removeMarker(location: Location) {
    console.log(location, 'removeMarker should be overridden');
  }

  removeLocation(location: Location) {
    this.removeMarker(location);
    const position = this.locations.indexOf(location);
    if (position > -1) {
      this.locations.splice(position, 1);
    }
  }

  /**
   * Process single location
   */
  processLocation(this: FrontendMap, location: Location) {
    let icon = '';
    if (location.information.icon) {
      icon = location.information.icon;
    } else if (Object.prototype.hasOwnProperty.call(this.mapConfiguration, 'markerIcon')) {
      icon = this.mapConfiguration.markerIcon;
    }

    this.locationIndex++;
    location.information.index = this.locationIndex;
    location.marker = this.createMarker(location, icon);
  }

  /**
   * Initialize location marker on map
   */
  initializeLocations(this: FrontendMap, locations: Array<Location>) {
    locations.map(this.processLocation.bind(this));
  }

  initializeInfoWindow(): void {
    // do nothing.
  }

  closeInfoWindow(): void {
    // do nothing.
  }

  /* eslint-disable */
  openInfoWindow(index: number): void {
    // do nothing.
  }
  /* eslint-enable */

  /**
   * Initialize list click events
   */
  initializeListEvents(this: FrontendMap) {
    document.addEventListener('click', (event: Event) => {
      const target = event.target as HTMLLIElement;
      if (!target.matches('.tx-storefinder .resultList > li')) {
        return;
      }
      this.openInfoWindow(parseInt(target.dataset.index, 10));
    });
  }

  /**
   * Initialize info window template
   */
  initializeTemplates(this: FrontendMap) {
    this.infoWindowTemplate = document.getElementById('templateInfoWindow').innerHTML;
    Mustache.parse(this.infoWindowTemplate);

    document.addEventListener('click', (event: Event) => {
      const button = event.target as HTMLElement;
      if (!button.matches('.tx-storefinder .infoWindow .close')) {
        return;
      }
      if (typeof this.mapConfiguration.handleCloseButtonCallback === 'function') {
        this.mapConfiguration.handleCloseButtonCallback(button);
      } else {
        this.closeInfoWindow();
      }
    });
  }

  /**
   * Post load javascript files
   */
  postLoadScript() {
    this.initializeMap();
    this.initializeLayer();
    this.initializeLocations(this.locations);
    this.initializeInfoWindow();
    this.initializeTemplates();
    this.initializeListEvents();
  }

  /**
   * Create a promise that resolves once the given resource is loaded
   */
  createFilePromise(uri: string, integrity: string = '', crossOrigin: string = ''): Promise<string> {
    return new Promise((resolve, reject) => {
      let element: HTMLLinkElement|HTMLScriptElement;
      if (uri.match(/\.css/)) {
        element = document.createElement('link');
        element.rel = 'stylesheet';
        element.href = uri;
      } else {
        element = document.createElement('script');
        element.src = uri;
      }

      if (integrity.length > 0) {
        element.integrity = integrity;
      }

      if (crossOrigin.length > 0) {
        element.crossOrigin = crossOrigin;
      }

      element.onload = () => {
        resolve(uri);
      };
      element.onerror = () => {
        reject(uri);
      };
      document.head.appendChild(element);
    })
  }

  loadScript(): void {
    // do nothing.
  }
}
