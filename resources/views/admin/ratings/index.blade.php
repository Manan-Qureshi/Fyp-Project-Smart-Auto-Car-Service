@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h3 class="fw-bold mb-0">
            <i class="fas fa-comments text-primary me-2"></i>Customer Feedback & Complaints
        </h3>
        
        <div class="d-flex gap-2">
            <a href="{{ route('admin.ratings.index') }}" class="btn btn-outline-secondary rounded-pill px-3 {{ request('type') ? '' : 'active' }}">
                All ({{ $totalGeneral + $totalComplaints }})
            </a>
            <a href="{{ route('admin.ratings.index', ['type' => 'general']) }}" class="btn btn-outline-success rounded-pill px-3 {{ request('type') === 'general' ? 'active' : '' }}">
                <i class="fas fa-comment-alt me-1"></i>General Feedback ({{ $totalGeneral }})
            </a>
            <a href="{{ route('admin.ratings.index', ['type' => 'complaint']) }}" class="btn btn-outline-danger rounded-pill px-3 {{ request('type') === 'complaint' ? 'active' : '' }}">
                <i class="fas fa-exclamation-triangle me-1"></i>Complaints Only ({{ $totalComplaints }})
            </a>
        </div>
    </div>

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
                        <th>Review / Complaint Details</th>
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
                                    <i class="fas fa-comment-alt me-1"></i>General Review
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
                        <td style="max-width: 300px;">
                            @if($rating->review)
                                <span class="text-dark">{{ $rating->review }}</span>
                            @else
                                <span class="text-muted fst-italic">No review details provided</span>
                            @endif
                        </td>
                        <td class="small text-muted">
                            {{ $rating->created_at ? $rating->created_at->format('d M Y, h:i A') : 'N/A' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-comment-slash fa-3x mb-3 opacity-50"></i>
                            <h5>No feedback or complaints found</h5>
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
