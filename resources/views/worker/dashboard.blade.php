@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <h3 class="fw-bold mb-4"><i class="fas fa-hard-hat text-warning me-2"></i>My Assigned Bookings</h3>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($assignedBookings->isEmpty())
        <div class="text-center py-5">
            <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
            <h5 class="text-muted">No bookings assigned to you yet.</h5>
        </div>
    @else
    {{-- Info banner explaining rules --}}
    <div class="alert alert-info rounded-3 mb-4 d-flex align-items-center gap-2">
        <i class="fas fa-info-circle fs-5"></i>
        <div>
            Bookings are handled <strong>first-come, first-served</strong>.
            Status can only move forward: <strong>Assigned → In-Progress → Completed</strong>.
        </div>
    </div>

    <div class="row g-3">
        @foreach($assignedBookings as $b)
        @php
            $sc = match($b->status){
                'assigned'    => 'info',
                'in_progress' => 'primary',
                'completed'   => 'success',
                'cancelled'   => 'danger',
                default       => 'secondary'
            };
            // This booking is the one the worker is currently allowed to act on
            $isActionable = ($b->id === $firstActionableId);
        @endphp
        <div class="col-md-6 col-xl-4">
            <div class="glass-card p-3 rounded-4 {{ !$isActionable && !in_array($b->status, ['completed','cancelled']) ? 'opacity-75' : '' }}">
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-bold">#{{ str_pad($b->id, 5, '0', STR_PAD_LEFT) }}</span>
                    <span class="badge bg-{{ $sc }} rounded-pill text-capitalize">{{ str_replace('_', ' ', $b->status) }}</span>
                </div>
                <div class="fw-semibold mb-1">{{ optional($b->service)->name }}</div>
                <div class="text-muted small mb-1"><i class="fas fa-user me-1"></i>{{ optional($b->user)->name }}</div>
                <div class="text-muted small mb-1"><i class="fas fa-store me-1"></i>{{ optional($b->serviceProvider)->business_name }}</div>
                <div class="text-muted small mb-3"><i class="fas fa-clock me-1"></i>{{ $b->appointment_time?->format('d M Y h:i A') }}</div>

                {{-- Action button — only on the first actionable booking --}}
                @if($isActionable && $b->status === 'assigned')
                    <form action="{{ route('bookings.status', $b) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit" name="status" value="in_progress"
                                class="btn btn-sm btn-primary rounded-pill w-100">
                            <i class="fas fa-play me-1"></i> Start Job
                        </button>
                    </form>

                @elseif($isActionable && $b->status === 'in_progress')
                    <form action="{{ route('bookings.status', $b) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit" name="status" value="completed"
                                class="btn btn-sm btn-success rounded-pill w-100">
                            <i class="fas fa-check me-1"></i> Mark Complete
                        </button>
                    </form>

                @elseif(!in_array($b->status, ['completed', 'cancelled']) && !$isActionable)
                    {{-- Locked — earlier booking must be handled first --}}
                    <div class="text-muted small text-center border rounded-3 py-2">
                        <i class="fas fa-lock me-1"></i> Waiting for earlier booking to start first
                    </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
