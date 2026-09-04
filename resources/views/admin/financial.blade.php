@extends('layouts.app')
@section('content')
<div class="container-fluid px-4 py-3">
    <h3 class="fw-bold mb-4"><i class="fas fa-chart-line text-success me-2"></i>Financial Reports</h3>

    {{-- Summary cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="glass-card p-4 rounded-4 text-center">
                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                <div class="fs-2 fw-bold" id="stat-completed-bookings">{{ number_format($totalBookings) }}</div>
                <div class="text-muted">Completed Bookings</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card p-4 rounded-4 text-center">
                <i class="fas fa-hand-holding-usd fa-2x text-primary mb-2"></i>
                <div class="fs-2 fw-bold" id="stat-total-revenue">PKR {{ number_format($totalRevenue) }}</div>
                <div class="text-muted">Platform Commission (10%)</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card p-4 rounded-4 text-center">
                <i class="fas fa-store fa-2x text-warning mb-2"></i>
                <div class="fs-2 fw-bold" id="stat-total-earnings">PKR {{ number_format($totalEarnings) }}</div>
                <div class="text-muted">Provider Earnings</div>
            </div>
        </div>
    </div>

    {{-- Commissions Table --}}
    <div class="glass-card p-4 rounded-4 shadow">
        <h5 class="fw-bold mb-3">Commission Records</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr><th>Booking</th><th>Provider</th><th>Service</th><th>Total</th><th>Commission (10%)</th><th>Provider Earns</th><th>Date</th></tr>
                </thead>
                <tbody>
                @forelse($commissions as $c)
                <tr>
                    <td class="fw-semibold">#{{ str_pad(optional($c->booking)->id,5,'0',STR_PAD_LEFT) }}</td>
                    <td>{{ optional($c->serviceProvider)->business_name }}</td>
                    <td>{{ optional(optional($c->booking)->service)->name }}</td>
                    <td>PKR {{ number_format($c->total_amount) }}</td>
                    <td class="text-success fw-semibold">PKR {{ number_format($c->commission_amount) }}</td>
                    <td>PKR {{ number_format($c->provider_earning) }}</td>
                    <td class="text-muted small">{{ $c->created_at->format('d M Y') }}</td>
                </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">No commission records yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $commissions->links() }}
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
            if(d.stats){
                if(d.stats.totalBookings !== undefined) document.getElementById('stat-completed-bookings').innerText = d.stats.totalBookings;
                if(d.stats.totalRevenue !== undefined) document.getElementById('stat-total-revenue').innerText = d.stats.totalRevenue;
                if(d.stats.totalEarnings !== undefined) document.getElementById('stat-total-earnings').innerText = d.stats.totalEarnings;
            }
        })
        .catch(e=>console.error('Polling error', e));
    }, 5000);
</script>
@endpush
@endsection
