@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0"><i class="fas fa-tachometer-alt text-primary me-2"></i>Provider Dashboard</h3>
        <span class="badge bg-primary rounded-pill px-3 py-2 fs-6">{{ $provider->business_name }}</span>
    </div>



    {{-- Stats --}}
    <div class="row g-3 mb-4">
        @foreach([
            ['key'=>'total',     'label'=>'Total Bookings',       'val'=>$stats['total'],     'icon'=>'calendar-alt', 'color'=>'primary'],
            ['key'=>'pending',   'label'=>'Pending Confirmation', 'val'=>$stats['pending'],   'icon'=>'clock',        'color'=>'warning'],
            ['key'=>'active',    'label'=>'In Progress',          'val'=>$stats['active'],    'icon'=>'spinner',      'color'=>'info'],
            ['key'=>'completed', 'label'=>'Completed',            'val'=>$stats['completed'], 'icon'=>'check-circle', 'color'=>'success'],
        ] as $s)
        <div class="col-6 col-md-3">
            <div class="glass-card p-3 rounded-4 text-center">
                <i class="fas fa-{{ $s['icon'] }} fa-2x text-{{ $s['color'] }} mb-2"></i>
                <div class="fs-3 fw-bold" id="stat-{{ $s['key'] }}">{{ number_format($s['val']) }}</div>
                <div class="text-muted small">{{ $s['label'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Bookings Table --}}
    <div class="glass-card p-4 rounded-4 shadow">
        <h5 class="fw-bold mb-3">
            <i class="fas fa-calendar-alt me-2 text-primary"></i>
            {{ $filtered ? 'Filtered Bookings' : 'Recent Bookings (Last 5)' }}
        </h5>

        {{-- Filter Form --}}
        <form method="GET" action="{{ route('provider.dashboard') }}" class="row g-2 align-items-end mb-4">
            <div class="col-md-6 col-lg-4">
                <label class="form-label fw-semibold small text-muted text-uppercase">Date</label>
                <input type="date" name="filter_date" class="form-control rounded-3" value="{{ $filterDate }}"
                    placeholder="Select date">
            </div>
            <div class="col-md-6 col-lg-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-pill flex-grow-1">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
                @if($filtered)
                    <a href="{{ route('provider.dashboard') }}" class="btn btn-outline-secondary rounded-pill">Clear</a>
                @endif
            </div>
        </form>

        {{-- Bookings Table --}}
        @if($bookings->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-calendar-alt fa-3x mb-3 opacity-25"></i>
                <p>No bookings found.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Customer</th>
                            <th>Service</th>
                            <th>Car</th>
                            <th>Appointment</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Worker</th>
                        </tr>
                    </thead>
                    <tbody id="bookings-tbody">
                        @include("provider.partials.bookings_table_body")
                    </tbody>
                </table>
            </div>

            {{-- Pagination / Show All --}}
            @if($filtered)
                @if(!$showAll && $bookingTotal > 10)
                    <div class="text-center mt-3">
                        <p class="text-muted small mb-2">Showing 10 of {{ $bookingTotal }} bookings</p>
                        <a href="{{ route('provider.dashboard', array_merge(request()->query(), ['show_all' => 1])) }}"
                            class="btn btn-outline-primary rounded-pill px-4">
                            <i class="fas fa-chevron-down me-1"></i> Show All {{ $bookingTotal }} Bookings
                        </a>
                    </div>
                @elseif($showAll)
                    <div class="text-center mt-3">
                        <p class="text-muted small mb-2">Showing all {{ $bookingTotal }} bookings</p>
                        <a href="{{ route('provider.dashboard', array_diff_key(request()->query(), ['show_all' => ''])) }}"
                            class="btn btn-outline-secondary rounded-pill px-4">
                            <i class="fas fa-chevron-up me-1"></i> Show Fewer
                        </a>
                    </div>
                @endif
            @else
                <div class="text-center mt-3">
                    <p class="text-muted small mb-0">Showing 5 most recent bookings</p>
                </div>
            @endif
        @endif
    </div>
</div>

@push('scripts')
<script>
    setInterval(function(){
        fetch(window.location.href, {
            headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}
        })
        .then(r=>r.json())
        .then(d=>{
            if(d.html) document.getElementById('bookings-tbody').innerHTML=d.html;
            if(d.stats){
                if(d.stats.total !== undefined) document.getElementById('stat-total').innerText = d.stats.total;
                if(d.stats.pending !== undefined) document.getElementById('stat-pending').innerText = d.stats.pending;
                if(d.stats.active !== undefined) document.getElementById('stat-active').innerText = d.stats.active;
                if(d.stats.completed !== undefined) document.getElementById('stat-completed').innerText = d.stats.completed;
            }
        })
        .catch(e=>console.error('Polling error', e));
    }, 5000);
</script>
@endpush
@endsection

