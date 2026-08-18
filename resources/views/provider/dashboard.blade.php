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
            ['label'=>'Total Bookings',      'val'=>$stats['total'],     'icon'=>'calendar-alt', 'color'=>'primary'],
            ['label'=>'Pending Confirmation', 'val'=>$stats['pending'],   'icon'=>'clock',        'color'=>'warning'],
            ['label'=>'In Progress',          'val'=>$stats['active'],    'icon'=>'spinner',      'color'=>'info'],
            ['label'=>'Completed',            'val'=>$stats['completed'], 'icon'=>'check-circle', 'color'=>'success'],
        ] as $s)
        <div class="col-6 col-md-3">
            <div class="glass-card p-3 rounded-4 text-center">
                <i class="fas fa-{{ $s['icon'] }} fa-2x text-{{ $s['color'] }} mb-2"></i>
                <div class="fs-3 fw-bold">{{ $s['val'] }}</div>
                <div class="text-muted small">{{ $s['label'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Bookings Table --}}
    <div class="glass-card p-4 rounded-4 shadow">
        <h5 class="fw-bold mb-3"><i class="fas fa-list me-2"></i>Bookings</h5>

        @if($bookings->isEmpty())
            <p class="text-muted text-center py-3">No bookings yet.</p>
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
        .then(d=>{if(d.html) document.getElementById('bookings-tbody').innerHTML=d.html;})
        .catch(e=>console.error('Polling error', e));
    }, 10000); // 10 seconds
</script>
@endpush
@endsection

