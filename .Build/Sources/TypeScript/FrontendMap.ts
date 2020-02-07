/**
 * This file is developed by evoweb.
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

  initializeMap() {}

  initializeLayer() {}

  /**
   * Render content of the info window
   */
  renderInfoWindowContent(location: Location): string {
    return Mustache.render(this.infoWindowTemplate, location.information)
  }

  createMarker(location: Location, icon: string) {}

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
  initializeLocations(this: FrontendMap) {
    this.locations.map(this.processLocation.bind(this));
  }

  initializeInfoWindow() {}

  closeInfoWindow() {}

  openInfoWindow(this: FrontendMap, index: number) {}

  /**
   * Initialize list click events
   */
  initializeListEvents(this: FrontendMap) {
    $(document).on('click', '.tx-storefinder .resultList > li', (event: Event, $field: JQuery): void => {
      this.openInfoWindow($field.data('index'));
    });
  }

  /**
   * Initialize info window template
   */
  initializeTemplates(this: FrontendMap) {
    this.infoWindowTemplate = $('#templateInfoWindow').html();
    Mustache.parse(this.infoWindowTemplate);

    $(document).on('click', '.tx-storefinder .infoWindow .close', (event: Event, $closeButton: JQuery): void => {
      if (typeof this.mapConfiguration.renderSingleViewCallback === 'function') {
        this.mapConfiguration.handleCloseButtonCallback($closeButton);
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

  loadScript() {}
}

