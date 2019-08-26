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

import * as $ from 'jquery';

interface MapConfiguration {
  active: boolean,
  afterSearch: number;

  apiConsoleKey: string,
  language: string,
  apiUrl: string,

  apiV3Layers: string,
  kmlUrl: string,
  allowSensors: boolean,
  renderSingleViewCallback: object,
  handleCloseButtonCallback: object,
}

declare global {
  interface Window {
    google: any;
    mapConfiguration: MapConfiguration,
    locations: Array<object>
    StoreFinder: object
  }
}

/**
 * Module: TYPO3/CMS/StoreFinder/FrontendMap
 * contains all logic for the frontend map output
 * @exports TYPO3/CMS/StoreFinder/FrontendMap
 */
class FrontendMap {
  private map: object;
  private mapConfiguration: MapConfiguration;
  private locations: Array<object>;
  private infoWindow: object;
  private infoWindowTemplate: object;

  /**
   * The constructor, set the class properties default values
   */
  constructor(mapConfiguration: MapConfiguration, locations: Array<object>) {
    this.mapConfiguration = mapConfiguration || {
      active: false,
      afterSearch: 0,
      apiConsoleKey: '',
      apiUrl: '',
      apiV3Layers: '',
      kmlUrl: '',
      language: 'en',
      allowSensors: false,
      renderSingleViewCallback: null,
      handleCloseButtonCallback: null
    };

    this.loadScript();
  }

  postLoadScript() {

  }

  loadScript() {
    let self = this,
      apiUrl = 'https://maps.googleapis.com/maps/api/js?v=3.exp',
      parameter = '&key=' + this.mapConfiguration.apiConsoleKey
        + '&sensor=' + (this.mapConfiguration.allowSensors ? 'true' : 'false');

    if (self.mapConfiguration.language !== '') {
      parameter += '&language=' + self.mapConfiguration.language;
    }

    if (self.mapConfiguration.hasOwnProperty('apiUrl')) {
      apiUrl = self.mapConfiguration.apiUrl;
    }

    $.when(
      $.getScript(apiUrl + parameter)
    ).done(function () {
      function wait(this: FrontendMap) {
        if (typeof window.google !== 'undefined') {
          this.postLoadScript();
        } else {
          window.requestAnimationFrame(wait.bind(this));
        }
      }
      window.requestAnimationFrame(wait.bind(self));
    }).fail(function () {
      console.log('Failed loading google maps resources.');
    });
  }
}

$(document).ready(function () {
  if (typeof window.mapConfiguration == 'object' && window.mapConfiguration.active) {
    // make module public to be available for callback after load
    window.StoreFinder = new FrontendMap(
      window.mapConfiguration,
      window.locations
    );
  }
});

// return constructor
export = FrontendMap;
