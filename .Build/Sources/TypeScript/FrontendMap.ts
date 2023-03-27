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
  public mapConfiguration: MapConfiguration = {
    active: false,
    afterSearch: 0,
    center: {
      lat: 0,
      lng: 0
    },
    zoom: 18,

    apiConsoleKey: '',
    apiUrl: '',
    language: '',

    markerIcon: '',
    apiV3Layers: '',
    kmlUrl: '',

    renderSingleViewCallback: null,
    handleCloseButtonCallback: null
  };
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

  initializeMap() {
    // do nothing.
  }

  initializeLayer() {
    // do nothing.
  }

  /**
   * Render content of the info window
   */
  renderInfoWindowContent(location: Location): string {
    return Mustache.render(this.infoWindowTemplate, location.information)
  }

  /* eslint-disable */
  createMarker(location: Location, icon: string) {
    // do nothing.
  }
  /* eslint-enable */

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
  initializeLocations(this: FrontendMap) {
    this.locations.map(this.processLocation.bind(this));
  }

  initializeInfoWindow() {
    // do nothing.
  }

  closeInfoWindow() {
    // do nothing.
  }

  /* eslint-disable */
  openInfoWindow(this: FrontendMap, index: number) {
    // do nothing.
  }
  /* eslint-enable */

  /**
   * Initialize list click events
   */
  initializeListEvents(this: FrontendMap) {
    $(document).on('click', (event: Event) => {
      if (!$(event.target).is('.tx-storefinder .resultList > li')) {
        return;
      }
      this.openInfoWindow($(event.target).data('index'));
    });
  }

  /**
   * Initialize info window template
   */
  initializeTemplates(this: FrontendMap) {
    this.infoWindowTemplate = $('#templateInfoWindow').html();
    Mustache.parse(this.infoWindowTemplate);

    $(document).on('click', (event: Event) => {
      if (!$(event.target).is('.tx-storefinder .infoWindow .close')) {
        return;
      }
      if (typeof this.mapConfiguration.renderSingleViewCallback === 'function') {
        this.mapConfiguration.handleCloseButtonCallback($(event.target));
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
    this.initializeLocations();
    this.initializeInfoWindow();
    this.initializeTemplates();
    this.initializeListEvents();
  }

  loadScript() {
    // do nothing.
  }
}

