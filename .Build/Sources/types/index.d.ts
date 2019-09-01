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

declare module omnivore {
  function kml(url: string): any;
}

declare interface MapConfiguration {
  active: Boolean,
  afterSearch: number;
  center?: {
    lat: number,
    lng: number
  };
  zoom?: number;

  apiConsoleKey: string,
  apiUrl: string,
  allowSensors: boolean,
  language: string,

  markerIcon: string,
  apiV3Layers: string,
  kmlUrl: string,

  renderSingleViewCallback(location: object, template: string): void,
  handleCloseButtonCallback(button: object): void,
}

declare interface Window {
    mapConfiguration: MapConfiguration,
    locations: Array<any>
}

declare interface Location {
  name: string,
  lat: number,
  lng: number,
  information: {
    index: number,
    icon: string,
  },
  marker: any
}
