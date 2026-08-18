@extends('layouts.app')
@section('content')
<div class="container py-4" style="max-width:700px">
    <div class="glass-card p-4 rounded-4 shadow">
        <h4 class="fw-bold mb-4"><i class="fas fa-plus-circle text-primary me-2"></i>Add Service Provider</h4>
        <form action="{{ route('admin.providers.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <h6 class="fw-semibold text-muted text-uppercase mb-3" style="font-size:.75rem">Login Account</h6>
            <div class="row g-3 mb-3">
                <div class="col"><label class="form-label fw-semibold">Owner Name *</label><input type="text" name="name" class="form-control rounded-3" required value="{{ old('name') }}"></div>
                <div class="col"><label class="form-label fw-semibold">Email *</label><input type="email" name="email" class="form-control rounded-3" required value="{{ old('email') }}"></div>
            </div>
            <div class="mb-4"><label class="form-label fw-semibold">Password *</label><input type="password" name="password" class="form-control rounded-3" required></div>

            <h6 class="fw-semibold text-muted text-uppercase mb-3" style="font-size:.75rem">Business Details</h6>
            <div class="mb-3"><label class="form-label fw-semibold">Business Name *</label><input type="text" name="business_name" class="form-control rounded-3" required value="{{ old('business_name') }}"></div>
            <div class="mb-3"><label class="form-label fw-semibold">Description</label><textarea name="description" class="form-control rounded-3" rows="2">{{ old('description') }}</textarea></div>
            <div class="row g-3 mb-3">
                <div class="col"><label class="form-label fw-semibold">Phone</label><input type="text" name="phone" class="form-control rounded-3" value="{{ old('phone') }}"></div>
                <div class="col"><label class="form-label fw-semibold">Service Radius (km)</label><input type="number" name="service_radius_km" class="form-control rounded-3" value="{{ old('service_radius_km', 20) }}" min="1"></div>
            </div>
            <div class="mb-3"><label class="form-label fw-semibold">Full Address *</label><input type="text" name="address" class="form-control rounded-3" required id="addressInput" value="{{ old('address') }}"></div>
            <div class="row g-3 mb-3">
                <div class="col"><label class="form-label fw-semibold">Latitude *</label><input type="number" step="any" name="latitude" class="form-control rounded-3" required id="latInput" value="{{ old('latitude') }}" placeholder="e.g. 33.6844"></div>
                <div class="col"><label class="form-label fw-semibold">Longitude *</label><input type="number" step="any" name="longitude" class="form-control rounded-3" required id="lngInput" value="{{ old('longitude') }}" placeholder="e.g. 73.0479"></div>
            </div>
            <div class="mb-3 d-flex align-items-center gap-3">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" onclick="geocodeAddress()">
                    <i class="fas fa-map-marker-alt me-1"></i> Auto-detect from Address
                </button>
                <small class="text-muted">Or enter coordinates manually</small>
            </div>
            <div class="mb-3"><label class="form-label fw-semibold">Logo</label><input type="file" name="logo" class="form-control rounded-3" accept="image/*"></div>

            <h6 class="fw-semibold text-muted text-uppercase mb-3" style="font-size:.75rem">Working Hours</h6>
            <div class="row g-3 mb-3">
                <div class="col">
                    <label class="form-label fw-semibold">Open Time *</label>
                    <input type="time" name="open_time" class="form-control rounded-3" required value="{{ old('open_time', '08:00') }}">
                </div>
                <div class="col">
                    <label class="form-label fw-semibold">Close Time *</label>
                    <input type="time" name="close_time" class="form-control rounded-3" required value="{{ old('close_time', '18:00') }}">
                </div>
            </div>



            <div class="d-flex gap-2">
                <a href="{{ route('admin.providers.index') }}" class="btn btn-outline-secondary rounded-pill flex-grow-1">Cancel</a>
                <button type="submit" class="btn btn-primary rounded-pill flex-grow-1">Create Provider</button>
            </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
function geocodeAddress() {
    const addr = document.getElementById('addressInput').value.trim();
    if (!addr) { alert('Enter address first.'); return; }
    fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(addr)}&format=json&limit=1`)
        .then(r => r.json())
        .then(d => {
            if (d.length) {
                document.getElementById('latInput').value = parseFloat(d[0].lat).toFixed(7);
                document.getElementById('lngInput').value = parseFloat(d[0].lon).toFixed(7);
            } else { alert('Address not found. Enter coordinates manually.'); }
        });
}
</script>
@endpush
