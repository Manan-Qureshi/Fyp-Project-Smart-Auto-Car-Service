@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <h3 class="fw-bold mb-4"><i class="fas fa-hard-hat text-warning me-2"></i>My Assigned Bookings</h3>


    <div class="alert alert-info rounded-3 mb-4 d-flex align-items-center gap-2">
        <i class="fas fa-info-circle fs-5"></i>
        <div>
            Bookings are handled <strong>first-come, first-served</strong>.
            Status can only move forward: <strong>Assigned → In-Progress → Completed</strong>.
        </div>
    </div>
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

