@extends('layouts.app')
@section('content')
<div class="container py-3" style="max-width:850px">
    <div class="glass-card p-4 rounded-4 shadow-sm">
        <h5 class="fw-bold mb-3"><i class="fas fa-edit text-primary me-2"></i>Edit Provider: {{ $provider->business_name }}</h5>
        <form action="{{ route('admin.providers.update', $provider) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            
            <!-- Business Details Section -->
            <h6 class="fw-semibold text-muted text-uppercase mb-2" style="font-size:.7rem">1. Business Details</h6>
            <div class="row g-2 mb-2">
                <div class="col-md-4"><label class="form-label fw-semibold small mb-1">Business Name *</label><input type="text" name="business_name" class="form-control form-control-sm rounded-3" required value="{{ old('business_name', $provider->business_name) }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold small mb-1">Phone</label><input type="text" name="phone" class="form-control form-control-sm rounded-3" value="{{ old('phone', $provider->phone) }}"></div>
                <div class="col-md-4"><label class="form-label fw-semibold small mb-1">Service Radius (km)</label><input type="number" name="service_radius_km" class="form-control form-control-sm rounded-3" value="{{ old('service_radius_km', $provider->service_radius_km) }}" min="1"></div>
            </div>
            
            <div class="row g-2 mb-3">
                <div class="col-md-6"><label class="form-label fw-semibold small mb-1">Description</label><input type="text" name="description" class="form-control form-control-sm rounded-3" value="{{ old('description', $provider->description) }}"></div>
                <div class="col-md-6"><label class="form-label fw-semibold small mb-1">Logo</label><input type="file" name="logo" class="form-control form-control-sm rounded-3" accept="image/*"></div>
            </div>

            <!-- Working Hours Section -->
            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold small mb-1">Open Time *</label>
                    <input type="time" name="open_time" class="form-control form-control-sm rounded-3" required value="{{ old('open_time', \Carbon\Carbon::parse($provider->open_time)->format('H:i')) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small mb-1">Close Time *</label>
                    <input type="time" name="close_time" class="form-control form-control-sm rounded-3" required value="{{ old('close_time', \Carbon\Carbon::parse($provider->close_time)->format('H:i')) }}">
                </div>
            </div>

            <!-- Location Details Section -->
            <h6 class="fw-semibold text-muted text-uppercase mb-2" style="font-size:.7rem">2. Location & Coordinates</h6>
            <div class="row g-3 mb-3">
                <!-- Map Left Column -->
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label fw-semibold small mb-0">Map Location</label>
                        <button type="button" class="btn btn-link text-primary p-0 btn-sm text-decoration-none small" style="font-size:.75rem" onclick="detectMyLocation()">
                            <i class="fas fa-crosshairs me-1"></i> Use My GPS
                        </button>
                    </div>
                    <div id="map" style="height: 170px; width: 100%; border-radius: 10px; border: 1px solid #ccc; z-index: 1;"></div>
                </div>

                <!-- Address & Lat/Lng Right Column -->
                <div class="col-md-6 d-flex flex-column justify-content-between">
                    <div>
                        <label class="form-label fw-semibold small mb-1">Full Address *</label>
                        <div class="input-group input-group-sm mb-2">
                            <input type="text" name="address" class="form-control rounded-start-3" required id="addressInput" value="{{ old('address', $provider->address) }}" placeholder="Type address & blur/click search">
                            <button type="button" class="btn btn-outline-secondary" onclick="geocodeAddress()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>

                    <div class="row g-2 mb-1">
                        <div class="col">
                            <label class="form-label fw-semibold small mb-1">Latitude *</label>
                            <input type="number" step="any" name="latitude" class="form-control form-control-sm rounded-3" required id="latInput" value="{{ old('latitude', $provider->latitude) }}">
                        </div>
                        <div class="col">
                            <label class="form-label fw-semibold small mb-1">Longitude *</label>
                            <input type="number" step="any" name="longitude" class="form-control form-control-sm rounded-3" required id="lngInput" value="{{ old('longitude', $provider->longitude) }}">
                        </div>
                    </div>
                    <small class="text-muted" style="font-size: 0.72rem;">
                        <i class="fas fa-info-circle me-1"></i>Address will auto-detect coordinates on blur or map drag.
                    </small>
                </div>
            </div>

            <div class="d-flex gap-2 pt-2">
                <a href="{{ route('admin.providers.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill flex-grow-1">Cancel</a>
                <button type="submit" class="btn btn-primary btn-sm rounded-pill flex-grow-1">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<link class="styles" rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
let map, marker;

document.addEventListener("DOMContentLoaded", function () {
    const defaultLat = parseFloat("{{ old('latitude', $provider->latitude) }}") || 30.3753;
    const defaultLng = parseFloat("{{ old('longitude', $provider->longitude) }}") || 69.3451;
    const defaultZoom = 15;

    map = L.map('map').setView([defaultLat, defaultLng], defaultZoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    }).addTo(map);

    const pos = [defaultLat, defaultLng];
    marker = L.marker(pos, { draggable: true }).addTo(map);
    setupMarkerEvents();

    map.on('click', function (e) {
        const lat = e.latlng.lat.toFixed(7);
        const lng = e.latlng.lng.toFixed(7);
        updateCoordinates(lat, lng);
        if (marker) {
            marker.setLatLng(e.latlng);
        } else {
            marker = L.marker(e.latlng, { draggable: true }).addTo(map);
            setupMarkerEvents();
        }
    });

    const addrInp = document.getElementById('addressInput');
    if (addrInp) {
        addrInp.addEventListener('change', function () {
            if (this.value.trim().length > 3) {
                geocodeAddress();
            }
        });
    }
});

function setupMarkerEvents() {
    if (!marker) return;
    marker.on('dragend', function () {
        const lat = marker.getLatLng().lat.toFixed(7);
        const lng = marker.getLatLng().lng.toFixed(7);
        updateCoordinates(lat, lng);
    });
}

function updateCoordinates(lat, lng) {
    document.getElementById('latInput').value = lat;
    document.getElementById('lngInput').value = lng;
}

function detectMyLocation() {
    if (!navigator.geolocation) {
        alert('Geolocation is not supported by your browser.');
        return;
    }
    navigator.geolocation.getCurrentPosition(function (position) {
        const lat = position.coords.latitude.toFixed(7);
        const lng = position.coords.longitude.toFixed(7);
        updateCoordinates(lat, lng);
        const pos = [lat, lng];
        map.setView(pos, 15);
        if (marker) {
            marker.setLatLng(pos);
        } else {
            marker = L.marker(pos, { draggable: true }).addTo(map);
            setupMarkerEvents();
        }
    }, function (error) {
        alert('Failed to detect location: ' + error.message);
    });
}

function geocodeAddress() {
    let addr = document.getElementById('addressInput').value.trim();
    if (!addr) { alert('Enter address first.'); return; }

    // Strip Google Plus Codes
    if (addr.includes('+')) {
        const parts = addr.split(',');
        if (parts.length > 1) {
            addr = parts.slice(1).join(',').trim();
        } else {
            addr = addr.replace('+', ' ').trim();
        }
    }

    fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(addr)}&format=json&limit=1`, {
        headers: {
            'User-Agent': 'CarCareHub/1.0 (autocarcarehub@gmail.com)'
        }
    })
    .then(r => r.json())
    .then(d => {
        if (d.length) {
            const lat = parseFloat(d[0].lat).toFixed(7);
            const lng = parseFloat(d[0].lon).toFixed(7);
            updateCoordinates(lat, lng);
            const pos = [lat, lng];
            map.setView(pos, 15);
            if (marker) {
                marker.setLatLng(pos);
            } else {
                marker = L.marker(pos, { draggable: true }).addTo(map);
                setupMarkerEvents();
            }
        } else {
            alert('Address not found. Please drag map or click to place marker manually.');
        }
    })
    .catch(() => {
        alert('Error contacting search service. Please click map manually.');
    });
}
</script>
@endpush
