import L from 'leaflet';
const H_LATITUDE = document.getElementById('latitude') as HTMLInputElement;
const H_LONGITUDE = document.getElementById('longitude') as HTMLInputElement;
const H_ADDRESS = document.getElementById('address') as HTMLInputElement;
const H_UPDATE_ADDRESS_FORM = document.getElementById('update-address') as HTMLFormElement;
const H_ELEMENTS = [H_LATITUDE, H_LONGITUDE, H_ADDRESS, H_UPDATE_ADDRESS_FORM];
function toggleWorkingHours(event: Event) {
    const target = event.target as HTMLInputElement;
    const ignore = target.checked;
    if (target.parentElement === null) {
        throw new Error('Bład strony, skontaktuj się z administratorem. Parent error.');
    }
    target.parentElement.querySelectorAll('input[type="time"]').forEach(input => {
        if (ignore) {
            input.setAttribute('disabled', 'disabled');
        } else {
            input.removeAttribute('disabled');
        }
    }
    );
}


for (const element of H_ELEMENTS) {
    if (element === null) {
        throw new Error('Bład strony, skontaktuj się z administratorem. Element error.');
    }
}
let LAT = parseFloat(H_LATITUDE.value);
let LON = parseFloat(H_LONGITUDE.value);
if (isNaN(LAT) || isNaN(LON)) {
    throw new Error('Bład strony, skontaktuj się z administratorem. Parse error.');
} else {
    let MAP = L.map('map', {
        //@ts-ignore This comes from the leaflet.fullscreen package
        attributionControl: false
    })
        .setView([LAT, LON], 18);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(MAP);
    let marker = L.marker([LAT, LON]).addTo(MAP);
}

function getLocation() {
    const address = H_ADDRESS.value;
    if (address === '') {
        throw new Error('Bład strony, skontaktuj się z administratorem. Address error.');
        return;
    }
    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${address}`;
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.length === 0) {
                throw new Error('Bład strony, skontaktuj się z administratorem. Data error.');
                return;
            }
            const address = data[0].display_name;
            const lat = data[0].lat;
            const lon = data[0].lon;
            H_LATITUDE.value = lat;
            H_LONGITUDE.value = lon;
            H_ADDRESS.value = address;
            // send form
            H_UPDATE_ADDRESS_FORM.submit();
        });
}

(window as any).getLocation = getLocation;
(window as any).toggleWorkingHours = toggleWorkingHours;    