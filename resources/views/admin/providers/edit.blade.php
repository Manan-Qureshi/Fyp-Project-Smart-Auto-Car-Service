@extends('layouts.app')
@section('content')
<div class="container py-4" style="max-width:700px">
    <div class="glass-card p-4 rounded-4 shadow">
        <h4 class="fw-bold mb-4"><i class="fas fa-edit text-primary me-2"></i>Edit Provider: {{ $provider->business_name }}</h4>
        <form action="{{ route('admin.providers.update', $provider) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="mb-3"><label class="form-label fw-semibold">Business Name *</label><input type="text" name="business_name" class="form-control rounded-3" required value="{{ old('business_name', $provider->business_name) }}"></div>
            <div class="mb-3"><label class="form-label fw-semibold">Description</label><textarea name="description" class="form-control rounded-3" rows="2">{{ old('description', $provider->description) }}</textarea></div>
            <div class="row g-3 mb-3">
                <div class="col"><label class="form-label fw-semibold">Phone</label><input type="text" name="phone" class="form-control rounded-3" value="{{ old('phone', $provider->phone) }}"></div>
                <div class="col"><label class="form-label fw-semibold">Service Radius (km)</label><input type="number" name="service_radius_km" class="form-control rounded-3" value="{{ old('service_radius_km', $provider->service_radius_km) }}" min="1"></div>
            </div>
            <div class="mb-3"><label class="form-label fw-semibold">Address *</label><input type="text" name="address" class="form-control rounded-3" required value="{{ old('address', $provider->address) }}"></div>
            <div class="row g-3 mb-3">
                <div class="col"><label class="form-label fw-semibold">Latitude *</label><input type="number" step="any" name="latitude" class="form-control rounded-3" required value="{{ old('latitude', $provider->latitude) }}"></div>
                <div class="col"><label class="form-label fw-semibold">Longitude *</label><input type="number" step="any" name="longitude" class="form-control rounded-3" required value="{{ old('longitude', $provider->longitude) }}"></div>
            </div>
            <div class="mb-3"><label class="form-label fw-semibold">Logo</label><input type="file" name="logo" class="form-control rounded-3" accept="image/*"></div>

            <h6 class="fw-semibold text-muted text-uppercase mb-3" style="font-size:.75rem">Working Hours</h6>
            <div class="row g-3 mb-3">
                <div class="col">
                    <label class="form-label fw-semibold">Open Time *</label>
                    <input type="time" name="open_time" class="form-control rounded-3" required
                           value="{{ old('open_time', \Carbon\Carbon::parse($provider->open_time)->format('H:i')) }}">
                </div>
                <div class="col">
                    <label class="form-label fw-semibold">Close Time *</label>
                    <input type="time" name="close_time" class="form-control rounded-3" required
                           value="{{ old('close_time', \Carbon\Carbon::parse($provider->close_time)->format('H:i')) }}">
                </div>
            </div>


            <div class="d-flex gap-2">
                <a href="{{ route('admin.providers.index') }}" class="btn btn-outline-secondary rounded-pill flex-grow-1">Cancel</a>
                <button type="submit" class="btn btn-primary rounded-pill flex-grow-1">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection
