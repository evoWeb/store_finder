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

    if (!Element.prototype.matches) {
      Element.prototype.matches = Element.prototype.matchesSelector ||
        Element.prototype.mozMatchesSelector ||
        Element.prototype.msMatchesSelector ||
        Element.prototype.oMatchesSelector ||
        Element.prototype.webkitMatchesSelector;
    }
  }

  initializeMap() {}

  initializeLayer() {}

  /**
   * Render content of the info window
   */
  renderInfoWindowContent(this: FrontendMap, location: Location): string {
    return Mustache.render(this.infoWindowTemplate, location.information)
  }

  createMarker(location: Location, icon: string) {}

  removeMarker(location: Location) {}

  removeLocation(location: Location) {
    this.removeMarker(location);
    let position = this.locations.indexOf(location);
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
    } else if (this.mapConfiguration.hasOwnProperty('markerIcon')) {
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

  initializeInfoWindow() {}

  closeInfoWindow() {}

  openInfoWindow(this: FrontendMap, index: number) {}

  /**
   * Initialize list click events
   */
  initializeListEvents(this: FrontendMap) {
    document.addEventListener('click', (event: Event) => {
      let field = event.target as HTMLLIElement,
        selector = '.tx-storefinder .resultList > li';
      if (field.matches(selector)) {
        this.openInfoWindow(parseInt(field.dataset.index));
      }
    });
  }

  /**
   * Initialize info window template
   */
  initializeTemplates(this: FrontendMap) {
    this.infoWindowTemplate = document.getElementById('templateInfoWindow').innerHTML;
    Mustache.parse(this.infoWindowTemplate);

    document.addEventListener('click', (event: Event) => {
      let button = event.target as HTMLButtonElement,
        selector = '.tx-storefinder .infoWindow .close';
      if (button.matches(selector)) {
        if (typeof this.mapConfiguration.handleCloseButtonCallback === 'function') {
          this.mapConfiguration.handleCloseButtonCallback(button);
        } else {
          this.closeInfoWindow();
        }
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

  loadScript() {}

  public static ajax(
    url: string,
    formData: FormData = null,
    successCallback: Function = null,
    errorCallback: Function = null
  ) {
    let request = new XMLHttpRequest();

    request.onreadystatechange = () => {
      if (request.readyState === 4) {
        if (request.status === 200) {
          if (successCallback) {
            successCallback(request.response);
          }
        } else {
          if (successCallback) {
            errorCallback(request.response);
          }
        }
      }
    }

    if (formData) {
      request.open('POST', url, true);
      request.send(formData);
    } else {
      request.open('GET', url, true);
      request.send();
    }
  }
}
