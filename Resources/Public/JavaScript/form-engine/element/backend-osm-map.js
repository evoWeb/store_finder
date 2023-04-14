import*as t from"@evoweb/store-finder/leaflet/leaflet-src.esm.js";import i from"jquery";class a{constructor(t){this.mapConfiguration={uid:"0",mapId:"",latitude:0,longitude:0,zoom:15},this.mapConfiguration=t,this.initializeMap(),this.initializeMarker(),this.initializeEvents(),setTimeout((()=>{this.map.invalidateSize()}),10)}initializeMap(){this.map=t.map(this.mapConfiguration.mapId),this.map.setView([this.mapConfiguration.latitude,this.mapConfiguration.longitude],this.mapConfiguration.zoom),this.map.doubleClickZoom.disable(),t.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",{maxZoom:19,attribution:'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'}).addTo(this.map)}initializeMarker(){this.marker=new t.Marker([this.mapConfiguration.latitude,this.mapConfiguration.longitude],{draggable:!0}),this.marker.bindPopup("").addTo(this.map)}initializeEvents(){i(".t3js-tabmenu-item a").on("click",(t=>{i("#"+i(t.target).attr("aria-controls")).trigger("cssActiveAdded")})),i("#map").parents(".tab-pane").on("cssActiveAdded",(()=>{setTimeout((()=>{this.map.invalidateSize()}),10)})),this.map.on("dblclick",(t=>{const i=t.latlng;return this.marker.setLatLng(i),this.updateCoordinateFields(i,this),!1})),this.marker.on("moveend",(t=>{const i=t.target.getLatLng();this.updateCoordinateFields(i,this)}))}updateCoordinateFields(t,a){const e="data[tx_storefinder_domain_model_location]["+a.mapConfiguration.uid+"]",o=i('*[data-formengine-input-name="'+e+'[latitude]"]'),n=i('*[data-formengine-input-name="'+e+'[longitude]"]');o.val(t.lat).trigger("change"),n.val(t.lng).trigger("change")}}export{a as default};