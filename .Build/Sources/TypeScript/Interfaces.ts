/* tslint:disable:max-classes-per-file */

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

export interface MapConfiguration {
  active: boolean,
  afterSearch: number;
  center?: {
    lat: number,
    lng: number
  };
  zoom?: string;

  apiConsoleKey: string,
  apiUrl: string,
  allowSensors: boolean,
  language: string,

  markerIcon: string,
  apiV3Layers: string,
  kmlUrl: string,

  renderSingleViewCallback(location: object, template: object): void,
  handleCloseButtonCallback(button: object): void,
}

export interface Location {
  name: string,
  lat: number,
  lng: number,
  information: {
    index: number,
    icon: string,
  },
  marker: any
}

export interface InfoWindow {
  close(): void,
  isOpen(): boolean,
  open(map: object, marker: any): void,
  closePopup(): void,
  setContent(content: string): void,
  setPosition(location: Location): void
}

export interface Template {
  render(information: any): string
}

declare global {
  interface Window {
    google: any,
    Hogan: any,
    L: any,
    mapConfiguration: MapConfiguration,
    locations: Array<any>,
    StoreFinder: object
  }
}
