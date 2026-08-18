@extends('layouts.app')
@section('content')
<div class="container-fluid px-4 py-3">
    <h3 class="fw-bold mb-4"><i class="fas fa-tools text-info me-2"></i>My Services</h3>
    
    <div class="glass-card p-4 rounded-4 shadow-sm mb-4">
        <h5 class="fw-bold mb-3"><i class="fas fa-clock text-info me-2"></i>Working Hours</h5>
        <form action="{{ route('provider.services.hours') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Start Time</label>
                    <input type="time" name="open_time" class="form-control rounded-3" value="{{ \Carbon\Carbon::parse($provider->open_time ?? '10:00:00')->format('H:i') }}" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-semibold">End Time</label>
                    <input type="time" name="close_time" class="form-control rounded-3" value="{{ \Carbon\Carbon::parse($provider->close_time ?? '16:00:00')->format('H:i') }}" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-info text-white w-100 rounded-pill"><i class="fas fa-save me-1"></i> Save Hours</button>
                </div>
            </div>
        </form>
    </div>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <p class="text-muted mb-0">Enable the services you offer. Prices are set globally and apply equally to all providers.</p>
        @php $categories = $allServices->pluck('category')->filter()->unique()->sort(); @endphp
        @if($categories->count() > 0)
        <div style="min-width: 250px;">
            <select class="form-select rounded-pill" id="providerCategoryFilter">
                <option value="all">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ strtolower(trim($cat)) }}">{{ $cat }}</option>
                @endforeach
                <option value="uncategorized">Uncategorized</option>
            </select>
        </div>
        @endif
    </div>

    <div class="glass-card p-4 rounded-4 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3 border-0">Service</th>
                        <th class="border-0">Base Price</th>
                        <th class="border-0">Duration</th>
                        <th class="border-0">Category</th>
                        <th class="border-0">Status</th>
                        <th class="text-end pe-3 border-0">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($allServices as $service)
                @php $enabled = in_array($service->id, $enabledIds); @endphp
                <tr class="service-card-wrapper {{ $enabled ? 'table-success opacity-100' : 'opacity-75' }}" data-category="{{ $service->category ? strtolower(trim($service->category)) : 'uncategorized' }}">
                    <td class="ps-3">
                        <div class="d-flex align-items-center gap-3">
                            @if($service->image)
                                <img src="{{ asset('storage/'.$service->image) }}" class="rounded-3 border" style="width: 45px; height: 45px; object-fit: cover;">
                            @else
                                <div class="rounded-3 bg-primary d-flex align-items-center justify-content-center text-white" style="width: 45px; height: 45px;">
                                    <i class="fas fa-concierge-bell"></i>
                                </div>
                            @endif
                            <span class="fw-bold">{{ $service->name }}</span>
                        </div>
                    </td>
                    <td class="fw-semibold text-muted">
                        PKR {{ number_format($service->base_price) }}
                    </td>
                    <td class="text-muted small">
                        <i class="fas fa-clock me-1"></i>{{ $service->duration_minutes }} min
                    </td>
                    <td>
                        @if($service->category)
                            <span class="badge bg-light text-dark border">{{ $service->category }}</span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $enabled ? 'bg-success' : 'bg-secondary' }} rounded-pill">
                            {{ $enabled ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="text-end pe-3">
                        <form action="{{ route('provider.services.toggle', $service) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm {{ $enabled ? 'btn-outline-danger' : 'btn-outline-success' }} rounded-pill">
                                <i class="fas {{ $enabled ? 'fa-times' : 'fa-check' }} me-1"></i>
                                {{ $enabled ? 'Disable Service' : 'Enable Service' }}
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('providerCategoryFilter')?.addEventListener('change', function() {
    const selected = this.value;
    document.querySelectorAll('.service-card-wrapper').forEach(card => {
        card.style.display = (selected === 'all' || card.dataset.category === selected) ? '' : 'none';
    });
});
</script>
@endpush
