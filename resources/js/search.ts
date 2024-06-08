import 'leaflet.fullscreen/Control.FullScreen.js';
import L from 'leaflet';
import { Communication } from './utils/Communication';
import { DataConv } from './utils/DataConv';
let H_INPUT_SEARCH = document.getElementById('search') as HTMLInputElement;
let H_INPUT_RANGE = document.getElementById('range') as HTMLInputElement;
let H_INPUT_RANGE_P = document.getElementById('rangeValue') as HTMLParagraphElement;
let H_RESULTS = document.getElementById('results') as HTMLDivElement;
let H_MAP = document.getElementById('map') as HTMLDivElement;
let H_RANGE = document.getElementById('range') as HTMLInputElement;
let H_ELEMENTS = [H_INPUT_SEARCH, H_INPUT_RANGE, H_RESULTS, H_RANGE, H_INPUT_RANGE_P, H_MAP];
for (let element of H_ELEMENTS) {
    if (element === null) {
        console.log('Element not found');
        throw new Error('Element not found');
    }
}
let MAP = L.map('map', {
    //@ts-ignore This comes from the leaflet.fullscreen package
    fullscreenControl: true,
    attributionControl: false
})
    .setView([52, 20], 6);
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(MAP);

updateRangeP(parseInt(H_INPUT_RANGE.value)); // Initial range

//Events
H_INPUT_SEARCH.addEventListener('keypress', (event) => {
    if (event.key === 'Enter') {
        searchLocation();
    }
});

function clearAllMarkers() {
    MAP.eachLayer((layer) => {
        if (layer instanceof L.Marker) {
            MAP.removeLayer(layer);
        }
    });
    MAP.eachLayer((layer) => {
        if (layer instanceof L.Circle) {
            MAP.removeLayer(layer);
        }
    });

}

function updateRangeP(range: number) {
    H_INPUT_RANGE_P.textContent = range.toString() + 'KM';
}

function addCityMarker(city: string, lat: number, lon: number) {
    let marker = L.marker([lat, lon]).addTo(MAP);
    marker.bindPopup(city);
}

function addDoctorMarker(doctor: string, img: string, lat: number, lon: number, result_id: number) {
    let markerOptions = {
        icon: L.icon({
            iconUrl: '../../storage/' + img,
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32]
        }),
        title: doctor
    };
    let marker = L.marker([lat, lon], markerOptions).addTo(MAP);
    marker.bindPopup(`<img class="profile" src="../../storage/${img}" alt="${doctor}"><br>${doctor}
    <hr><a href="#results-${result_id}">Pokaż na liście</a>`);
}

function searchLocation() {
    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${H_INPUT_SEARCH.value}`)
        .then((response) => {
            return response.json();
        }).then((data) => {
            if (data.length > 0) {
                MAP.setView([data[0].lat, data[0].lon], 13);
                clearAllMarkers();
                addCityMarker(data[0].display_name, data[0].lat, data[0].lon);
                getResults(H_INPUT_RANGE.value, data[0].lat, data[0].lon);
                addCircle(data[0].lat, data[0].lon, parseInt(H_INPUT_RANGE.value));
            }
        });
}

function addCircle(lat: number, lon: number, range: number) {
    let circle = L.circle([lat, lon], {
        color: 'blue',
        fillColor: '#00f',
        fillOpacity: 0.05,
        radius: range * 1000
    }).addTo(MAP);
}

function getResults(range: string, latitude: number, longitude: number) {
    let data = {
        range: range,
        latitude: latitude,
        longitude: longitude
    };
    let encoded = JSON.stringify(data);
    Communication.postData(encoded, 'getNearestOffers').then((response) => {
        console.log(response);
        data = JSON.parse(DataConv.base64ToString(response));
        addResults(data);
    }).catch((error) => {
        console.log(error);
    });
}


function findLocation() {
    navigator.geolocation.getCurrentPosition((position) => {
        MAP.setView([position.coords.latitude, position.coords.longitude], 13);
        clearAllMarkers();
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${position.coords.latitude}&lon=${position.coords.longitude}`)
            .then((response) => {
                return response.json();
            }).then((data) => {
                H_INPUT_SEARCH.value = data['display_name'];
                addCityMarker(data['display_name'], position.coords.latitude, position.coords.longitude);
            });
    });

}

function clearResults() {
    H_RESULTS.innerHTML = '';
}
function addResults(data: any) {
    H_MAP.hidden = false;
    MAP.invalidateSize();
    clearResults();
    for (let i = 0; i < data.length; i++) {
        addDoctorMarker(data[i].doctor_name + ' ' + data[i].doctor_surname, data[i].image, data[i].latitude, data[i].longitude, i);
        let div = document.createElement('div');
        div.classList.add('grid');
        div.id = 'results-' + i;
        let img = document.createElement('img');
        img.classList.add('profile-big');
        img.src = "../../storage/" + data[i].image;
        img.alt = data[i].doctor_name + ' ' + data[i].doctor_surname;
        let header = document.createElement('header');
        let h3 = document.createElement('h3');
        h3.textContent = data[i].doctor_name + ' ' + data[i].doctor_surname;
        let p = document.createElement('p');
        p.textContent = data[i].description;
        let addressDiv = document.createElement('div');
        let addressH = document.createElement('h4');
        addressH.textContent = 'Adres: ';
        let addressP = document.createElement('p');
        addressP.textContent = data[i].address;
        let aAddress = document.createElement('a');
        aAddress.textContent = "Pokaż na mapie wyżej";
        aAddress.href = '#map';
        aAddress.addEventListener('click', () => {
            MAP.setView([data[i].latitude, data[i].longitude], 13);
            MAP.eachLayer((layer) => {
                if (layer instanceof L.Marker) {
                    if (layer.getLatLng().lat === data[i].latitude && layer.getLatLng().lng === data[i].longitude) {
                        layer.openPopup();
                    }
                }
            });
        });
        addressDiv.appendChild(addressH);
        addressDiv.appendChild(addressP);
        addressDiv.appendChild(aAddress);
        let profileLinkDiv = document.createElement('div');
        let profileH = document.createElement('h4');
        profileH.textContent = 'Zobacz profil';
        profileLinkDiv.appendChild(profileH);
        let a = document.createElement('a');
        a.href = '/offers/show/' + data[i].id;
        a.target = '_blank';
        a.role = 'button';
        a.textContent = 'Przejdź do oferty';
        header.appendChild(h3);
        header.appendChild(p);
        div.appendChild(img);
        div.appendChild(header);
        div.appendChild(addressDiv);
        H_RESULTS.appendChild(div);
        profileLinkDiv.appendChild(a);
        div.appendChild(profileLinkDiv);
    }
}

// Global scope
(window as any).findLocation = findLocation;
(window as any).searchLocation = searchLocation;
(window as any).updateRange = updateRangeP;