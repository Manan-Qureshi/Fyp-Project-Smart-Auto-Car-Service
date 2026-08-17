@extends('layouts.app')
@section('content')
<div class="container-fluid px-4 py-3">
    <h3 class="fw-bold mb-4"><i class="fas fa-concierge-bell text-primary me-2"></i>Services</h3>

    <div class="row g-4 mb-4">
        {{-- ── COLUMN 1: MANAGERS ── --}}
        <div class="col-lg-5 d-flex flex-column gap-4">
            
            {{-- TIME DURATION MANAGER --}}
            <div class="glass-card p-4 rounded-4 shadow-sm flex-fill">
                <h6 class="fw-bold mb-3 text-uppercase" style="font-size:.75rem; letter-spacing:.05em">
                    <i class="fas fa-clock text-primary me-2"></i>Time Durations
                </h6>

                {{-- Add Duration Form --}}
                <form method="POST" action="/admin/durations" class="mb-3">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label fw-semibold small">Duration (minutes)</label>
                        <input type="number" name="minutes" class="form-control form-control-sm rounded-3"
                               min="5" max="480" step="5" required placeholder="e.g. 45">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold small">Label</label>
                        <input type="text" name="label" class="form-control form-control-sm rounded-3"
                               required placeholder="e.g. 45 Minutes"
                               id="durationLabelInput">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill w-100">
                        <i class="fas fa-plus me-1"></i> Add Duration
                    </button>
                </form>

                <hr class="my-3">

                {{-- Duration List --}}
                <div class="d-flex flex-wrap gap-2" style="max-height: 150px; overflow-y: auto;">
                    @forelse($durations as $dur)
                    <div class="d-flex align-items-center py-1 px-2 rounded-pill"
                         style="background:rgba(102,126,234,.08); border:1px solid rgba(102,126,234,.15); width: max-content;">
                        <span class="fw-semibold small" style="font-size: .75rem;">
                            <i class="fas fa-stopwatch text-primary me-1"></i>
                            {{ $dur->label }}
                        </span>
                        <form method="POST" action="/admin/durations/{{ $dur->id }}"
                              class="ms-2 mb-0 d-flex align-items-center">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger rounded-circle p-0 d-flex align-items-center justify-content-center border-0" style="width: 16px; height: 16px; font-size:.6rem">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                    </div>
                    @empty
                    <p class="text-muted small text-center">No durations yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- CATEGORY MANAGER --}}
            <div class="glass-card p-4 rounded-4 shadow-sm flex-fill">
                <h6 class="fw-bold mb-3 text-uppercase" style="font-size:.75rem; letter-spacing:.05em">
                    <i class="fas fa-tags text-primary me-2"></i>Service Categories
                </h6>

                {{-- Add Category Form --}}
                <form method="POST" action="/admin/categories" class="mb-3">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label fw-semibold small">Category Name</label>
                        <input type="text" name="name" class="form-control form-control-sm rounded-3"
                               required placeholder="e.g. Maintenance">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill w-100">
                        <i class="fas fa-plus me-1"></i> Add Category
                    </button>
                </form>

                <hr class="my-3">

                {{-- Category List --}}
                <div class="d-flex flex-wrap gap-2" style="max-height: 150px; overflow-y: auto;">
                    @forelse($serviceCategories as $cat)
                    <div class="d-flex align-items-center py-1 px-2 rounded-pill"
                         style="background:rgba(102,126,234,.08); border:1px solid rgba(102,126,234,.15); width: max-content;">
                        <span class="fw-semibold small" style="font-size: .75rem;">
                            <i class="fas fa-tag text-primary me-1"></i>
                            {{ $cat->name }}
                        </span>
                        <form method="POST" action="/admin/categories/{{ $cat->id }}"
                              class="ms-2 mb-0 d-flex align-items-center">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger rounded-circle p-0 d-flex align-items-center justify-content-center border-0" style="width: 16px; height: 16px; font-size:.6rem">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                    </div>
                    @empty
                    <p class="text-muted small text-center">No categories yet.</p>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- ── COLUMN 2: ADD / EDIT SERVICE FORM ── --}}
        <div class="col-lg-7">
            <div class="glass-card p-4 rounded-4 shadow-sm">
                <h5 class="fw-bold mb-3">
                    @if(isset($editService))
                        <i class="fas fa-edit text-warning me-2"></i>Edit Service
                    @else
                        <i class="fas fa-plus-circle text-primary me-2"></i>Add New Service
                    @endif
                </h5>

                @if(isset($editService))
                    <form method="POST" action="/admin/services/{{ $editService->id }}" enctype="multipart/form-data">
                        @method('PUT')
                @else
                    <form method="POST" action="/admin/services" enctype="multipart/form-data">
                @endif
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">Service Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control rounded-3 @error('name') is-invalid @enderror"
                           value="{{ old('name', $editService->name ?? '') }}" required placeholder="e.g. Oil Change">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                    @if($serviceCategories->isEmpty())
                        <div class="alert alert-warning rounded-3 p-2 small">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Add at least one category from the left panel first.
                        </div>
                    @else
                        <select name="category" class="form-select rounded-3" required>
                            <option value="">-- Select Category --</option>
                            @foreach($serviceCategories as $cat)
                                <option value="{{ $cat->name }}" {{ old('category', $editService->category ?? '') == $cat->name ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Base Price (PKR) <span class="text-danger">*</span></label>
                    <input type="number" name="base_price" class="form-control rounded-3 @error('base_price') is-invalid @enderror"
                           value="{{ old('base_price', $editService->base_price ?? '') }}" min="0" step="1" required placeholder="e.g. 2500">
                    @error('base_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Final price = base price × car model modifier</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Time Duration <span class="text-danger">*</span></label>
                    @if($durations->isEmpty())
                        <div class="alert alert-warning rounded-3 p-2 small">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Add at least one time duration from the left panel first.
                        </div>
                        <input type="hidden" name="duration_minutes" value="60">
                    @else
                        <select name="duration_minutes" class="form-select rounded-3" required>
                            <option value="">-- Select Duration --</option>
                            @foreach($durations as $dur)
                                <option value="{{ $dur->minutes }}"
                                    {{ old('duration_minutes', $editService->duration_minutes ?? '') == $dur->minutes ? 'selected' : '' }}>
                                    {{ $dur->label }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" class="form-control rounded-3" rows="3"
                              placeholder="Describe this service...">{{ old('description', $editService->description ?? '') }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Service Image</label>
                    <input type="file" name="image" class="form-control rounded-3" accept="image/*">
                    @if(isset($editService) && $editService->image)
                        <div class="mt-2"><img src="{{ asset('storage/'.$editService->image) }}" class="rounded-2" height="60"></div>
                    @endif
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill flex-grow-1">
                        <i class="fas fa-save me-1"></i>
                        {{ isset($editService) ? 'Update Service' : 'Add Service' }}
                    </button>
                    @if(isset($editService))
                        <a href="/admin/services" class="btn btn-outline-secondary rounded-pill">Cancel</a>
                    @endif
                </div>
                </form>
            </div>
        </div>

    </div>

    <div class="row g-4">
        {{-- ── COLUMN 3: SERVICES LIST ── --}}
        <div class="col-lg-12">
            <div class="glass-card p-4 rounded-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-list text-primary me-2"></i>All Services
                        <span class="badge bg-primary rounded-pill ms-1">{{ $services->count() }}</span>
                    </h5>
                    
                    @if($serviceCategories->count() > 0)
                    <div class="w-25">
                        <select class="form-select form-select-sm rounded-pill" id="adminCategoryFilter">
                            <option value="all">All Categories</option>
                            @foreach($serviceCategories as $cat)
                                <option value="{{ strtolower(trim($cat->name)) }}">{{ $cat->name }}</option>
                            @endforeach
                            <option value="uncategorized">Uncategorized</option>
                        </select>
                    </div>
                    @endif
                </div>

                @if($services->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-concierge-bell fa-3x mb-3 opacity-25"></i>
                        <p>No services yet. Use the form to add your first service.</p>
                    </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Duration</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($services as $service)
                        <tr class="service-row {{ isset($editService) && $editService->id === $service->id ? 'table-warning' : '' }}" data-category="{{ $service->category ? strtolower(trim($service->category)) : 'uncategorized' }}">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($service->image)
                                        <img src="{{ asset('storage/'.$service->image) }}" class="rounded-2" style="width: 45px; height: 45px; object-fit: cover;" alt="{{ $service->name }}">
                                    @else
                                        <div class="rounded-2 bg-light d-flex align-items-center justify-content-center text-muted border" style="width: 45px; height: 45px;">
                                            <i class="fas fa-concierge-bell"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold">{{ $service->name }}</div>
                                        @if($service->category)
                                        <span class="badge bg-light text-dark border" style="font-size:.65rem">{{ $service->category }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="fw-bold text-primary">PKR {{ number_format($service->base_price) }}</td>
                            <td class="text-muted small">{{ $service->duration_minutes }} min</td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="/admin/services?edit={{ $service->id }}"
                                       class="btn btn-sm btn-outline-warning rounded-pill">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form action="/admin/services/{{ $service->id }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger rounded-pill">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
// Auto-fill duration label as user types minutes
document.querySelector('input[name="minutes"]')?.addEventListener('input', function() {
    const mins = parseInt(this.value);
    if (!mins) return;
    let label = '';
    if (mins < 60) {
        label = mins + ' Minutes';
    } else if (mins % 60 === 0) {
        label = (mins / 60) + (mins === 60 ? ' Hour' : ' Hours');
    } else {
        label = Math.floor(mins/60) + ' Hour ' + (mins % 60) + ' Minutes';
    }
    document.getElementById('durationLabelInput').value = label;
});
// Category Filter Logic
document.getElementById('adminCategoryFilter')?.addEventListener('change', function() {
    const selected = this.value;
    document.querySelectorAll('.service-row').forEach(row => {
        row.style.display = (selected === 'all' || row.dataset.category === selected) ? '' : 'none';
    });
});

// Flash Message Popup
@if(session('swal_error') || session('error'))
Swal.fire({
    icon: 'error',
    title: 'Cannot Delete',
    text: '{{ session('swal_error') ?? session('error') }}',
    confirmButtonText: 'OK',
    confirmButtonColor: '#dc3545',
    width: '380px',
    customClass: { popup: 'rounded-4' }
});
@endif
</script>
@endpush
@endsection
