@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4 py-3">
        <h3 class="fw-bold mb-4"><i class="fas fa-hard-hat text-warning me-2"></i>My Assigned Bookings</h3>

        {{-- Stats --}}
        @if(isset($stats))
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="glass-card p-3 rounded-4 text-center">
                    <i class="fas fa-list-alt fa-2x text-primary mb-2"></i>
                    <div class="fs-3 fw-bold" id="stat-total">{{ $stats['total'] ?? 0 }}</div>
                    <div class="text-muted small">Total Assigned</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="glass-card p-3 rounded-4 text-center">
                    <i class="fas fa-user-clock fa-2x text-warning mb-2"></i>
                    <div class="fs-3 fw-bold" id="stat-assigned">{{ $stats['assigned'] ?? 0 }}</div>
                    <div class="text-muted small">Pending Start</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="glass-card p-3 rounded-4 text-center">
                    <i class="fas fa-spinner fa-2x text-info mb-2"></i>
                    <div class="fs-3 fw-bold" id="stat-active">{{ $stats['active'] ?? 0 }}</div>
                    <div class="text-muted small">In Progress</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="glass-card p-3 rounded-4 text-center">
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <div class="fs-3 fw-bold" id="stat-completed">{{ $stats['completed'] ?? 0 }}</div>
                    <div class="text-muted small">Completed</div>
                </div>
            </div>
        </div>
        @endif

        <div class="glass-card p-4 rounded-4 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Service</th>
                            <th>Customer</th>
                            <th>Provider</th>
                            <th>Appointment</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="bookings-tbody">
                        @include("worker.partials.bookings_table_body")
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            setInterval(function () {
                fetch(window.location.href, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                    .then(r => r.json())
                    .then(d => {
                        if (d.html) document.getElementById('bookings-tbody').innerHTML = d.html;
                        if (d.stats) {
                            if (d.stats.total !== undefined) document.getElementById('stat-total').innerText = d.stats.total;
                            if (d.stats.assigned !== undefined) document.getElementById('stat-assigned').innerText = d.stats.assigned;
                            if (d.stats.active !== undefined) document.getElementById('stat-active').innerText = d.stats.active;
                            if (d.stats.completed !== undefined) document.getElementById('stat-completed').innerText = d.stats.completed;
                        }
                    })
                    .catch(e => console.error('Polling error', e));
            }, 5000);
        </script>
    @endpush
@endsection