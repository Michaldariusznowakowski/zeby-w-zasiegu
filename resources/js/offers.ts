import L from 'leaflet';
let H_META_LOCATION = document.querySelector('meta[name="meta-location"]') as HTMLMetaElement;
let H_META_PHOTO = document.querySelector('meta[name="meta-photo"]') as HTMLMetaElement;
let H_ELEMENTS = [H_META_LOCATION, H_META_PHOTO];
for (const element of H_ELEMENTS) {
    if (element === null) {
        throw new Error('Element not found');
    }
}
let LAT = parseFloat(H_META_LOCATION.content.split(',')[0]);
let LON = parseFloat(H_META_LOCATION.content.split(',')[1]);
let MAP = L.map('map', {
    //@ts-ignore This comes from the leaflet.fullscreen package
    attributionControl: false
})
    .setView([LAT, LON], 18);
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(MAP);
let marker_options = {
    icon: L.icon({
        iconUrl: H_META_PHOTO.content,
        iconSize: [32, 32],
        iconAnchor: [16, 32],
        popupAnchor: [0, -32]
    }),
    title: 'Lokalizacja'
};
let marker = L.marker([LAT, LON], marker_options).addTo(MAP);
