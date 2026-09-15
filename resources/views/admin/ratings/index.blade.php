@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h3 class="fw-bold mb-0">
            <i class="fas fa-comment-dots text-primary me-2"></i>Customer Feedback
        </h3>
    </div>

    {{-- Filter Bar --}}
    <div class="glass-card p-3 rounded-4 shadow-sm mb-4">
        <form method="GET" action="{{ route('admin.ratings.index') }}" class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="form-label fw-semibold small mb-1">Service Provider</label>
                <select name="provider_id" class="form-select rounded-3" onchange="this.form.submit()">
                    <option value="">All Providers</option>
                    @foreach($providers as $provider)
                        <option value="{{ $provider->id }}" {{ request('provider_id') == $provider->id ? 'selected' : '' }}>
                            {{ $provider->business_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto ms-auto d-flex gap-2 align-items-end">
                <a href="{{ route('admin.ratings.index', array_filter(['provider_id' => request('provider_id')])) }}"
                   class="btn rounded-pill px-4 {{ !request('type') ? 'btn-secondary' : 'btn-outline-secondary' }}">
                    All ({{ $totalGeneral + $totalComplaints }})
                </a>
                <a href="{{ route('admin.ratings.index', array_filter(['type' => 'complaint', 'provider_id' => request('provider_id')])) }}"
                   class="btn rounded-pill px-4 {{ request('type') === 'complaint' ? 'btn-danger' : 'btn-outline-danger' }}">
                    <i class="fas fa-exclamation-triangle me-1"></i>Complaints ({{ $totalComplaints }})
                </a>
            </div>
        </form>
    </div>

    {{-- Feedback Table --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">ID</th>
                        <th>Type</th>
                        <th>Customer</th>
                        <th>Service & Provider</th>
                        <th>Rating</th>
                        <th>Review / Complaint</th>
                        <th>Submitted At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ratings as $rating)
                    <tr>
                        <td class="ps-3 fw-bold text-muted">#{{ str_pad($rating->id, 5, '0', STR_PAD_LEFT) }}</td>
                        <td>
                            @if($rating->feedback_type === 'complaint')
                                <span class="badge bg-danger rounded-pill px-3 py-1">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Complaint
                                </span>
                            @else
                                <span class="badge bg-success rounded-pill px-3 py-1">
                                    <i class="fas fa-comment-alt me-1"></i>General
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold">{{ optional($rating->customer)->name ?? 'Guest' }}</div>
                            <small class="text-muted">{{ optional($rating->customer)->email }}</small>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ optional(optional($rating->booking)->service)->name ?? 'Service' }}</div>
                            <small class="text-muted"><i class="fas fa-store me-1"></i>{{ optional($rating->serviceProvider)->business_name }}</small>
                        </td>
                        <td>
                            <span class="badge bg-warning text-dark rounded-pill px-2 py-1 fw-bold">
                                {{ $rating->rating }} ★
                            </span>
                        </td>
                        <td style="max-width: 280px;">
                            @if($rating->review)
                                <span class="text-dark">{{ $rating->review }}</span>
                            @else
                                <span class="text-muted fst-italic">No details provided</span>
                            @endif
                        </td>
                        <td class="small text-muted">
                            {{ $rating->created_at ? $rating->created_at->format('d M Y, h:i A') : 'N/A' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-comment-slash fa-3x mb-3 opacity-50 d-block"></i>
                            <h5>No feedback found</h5>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($ratings->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $ratings->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
