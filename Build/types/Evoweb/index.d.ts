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

declare module omnivore {
  function kml(url: string): any;
}

declare module '@evoweb/store-finder/leaflet/leaflet-src.esm.js' {
  import * as leaflet from 'leaflet';

  class LatLng extends leaflet.LatLng {}
  interface LeafletEvent extends leaflet.LeafletEvent {}
  interface LeafletMouseEvent extends leaflet.LeafletMouseEvent {}
  class Map extends leaflet.Map {}
  class Marker extends leaflet.Marker {}

  function map(element: string | HTMLElement, options?: leaflet.MapOptions): leaflet.Map;
  function tileLayer(urlTemplate: string, options?: leaflet.TileLayerOptions): leaflet.TileLayer;
}

declare interface MapConfiguration {
  active: boolean,
  afterSearch: number;
  center?: {
    lat: number,
    lng: number
  };
  zoom?: number;

  apiConsoleKey: string,
  language: string,
  mapId?: string,
  libraries?: Array<string>,

  markerIcon: string,
  apiV3Layers: string,
  kmlUrl: string,
  mapStyles?: google.maps.MapTypeStyle[],

  attribution?: string,
  style?: string,

  renderSingleViewCallback(location: object, template: string): void,
  handleCloseButtonCallback(button: object): void,
}

declare interface MarkerOptions {
  map: google.maps.Map;
  title: string;
  position: google.maps.LatLng;
  content?: HTMLElement;
}

declare interface BackendConfiguration {
  uid: string,
  mapId: string,
  latitude: number,
  longitude: number,
  zoom: number
}

declare interface Window {
  mapConfiguration: MapConfiguration,
  locations: Array<any>,
  sfas: any
}

interface Element {
  msMatchesSelector(selectors: string): boolean;
}

declare interface Location {
  name: string,
  lat: number,
  lng: number,
  information: {
    uid: number,
    index: number,
    icon: string,
  },
  marker: any
}
