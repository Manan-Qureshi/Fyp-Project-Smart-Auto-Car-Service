@extends('layouts.app')
@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0"><i class="fas fa-chart-line text-success me-2"></i>Financial & Commission Reports</h3>
            <p class="text-muted small mb-0">Track platform commissions, provider earnings, and filter records by date and provider.</p>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="glass-card p-4 rounded-4 text-center shadow-sm">
                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                <div class="fs-2 fw-bold" id="stat-completed-bookings">{{ number_format($totalBookings) }}</div>
                <div class="text-muted">Completed Bookings</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card p-4 rounded-4 text-center shadow-sm">
                <i class="fas fa-hand-holding-usd fa-2x text-primary mb-2"></i>
                <div class="fs-2 fw-bold" id="stat-total-revenue">PKR {{ number_format($totalRevenue) }}</div>
                <div class="text-muted">Platform Commission (10%)</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card p-4 rounded-4 text-center shadow-sm">
                <i class="fas fa-store fa-2x text-warning mb-2"></i>
                <div class="fs-2 fw-bold" id="stat-total-earnings">PKR {{ number_format($totalEarnings) }}</div>
                <div class="text-muted">Provider Earnings</div>
            </div>
        </div>
    </div>

    {{-- Main Filter & Bookings Card (Matching Picture 2 Design) --}}
    <div class="glass-card p-4 rounded-4 shadow-sm mb-4 bg-white">
        <div class="d-flex align-items-center mb-3">
            <i class="fas fa-calendar-alt text-primary fa-lg me-2"></i>
            <h5 class="fw-bold mb-0">Recent Bookings (Last 5)</h5>
        </div>

        {{-- Filter Form --}}
        <form action="{{ route('admin.financial') }}" method="GET" class="row g-3 align-items-end mb-4">
            <div class="col-md-4 col-sm-6">
                <label class="form-label text-uppercase text-muted fw-bold small mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">DATE</label>
                <input type="date" name="date" class="form-control rounded-3" value="{{ request('date', $startDate) }}">
            </div>
            <div class="col-md-5 col-sm-6">
                <label class="form-label text-uppercase text-muted fw-bold small mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">SERVICE PROVIDER</label>
                <select name="provider_id" class="form-select rounded-3">
                    <option value="">All Providers</option>
                    @foreach($providers as $provider)
                        <option value="{{ $provider->id }}" {{ (string)$providerId === (string)$provider->id ? 'selected' : '' }}>
                            {{ $provider->business_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-sm-12 d-flex gap-2">
                <button type="submit" class="btn text-white rounded-pill px-4 py-2 flex-grow-1 shadow-sm" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border: none;">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
                @if(request('date') || request('provider_id') || request('start_date') || request('end_date'))
                    <a href="{{ route('admin.financial') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2" title="Clear Filters">
                        <i class="fas fa-undo"></i>
                    </a>
                @endif
            </div>
        </form>

        {{-- Table or Empty State --}}
        @if($commissions->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Booking</th>
                            <th>Provider</th>
                            <th>Service</th>
                            <th>Total</th>
                            <th>Commission (10%)</th>
                            <th>Provider Earns</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($commissions as $c)
                    <tr>
                        <td class="fw-semibold">#{{ str_pad(optional($c->booking)->id, 5, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ optional($c->serviceProvider)->business_name }}</td>
                        <td>{{ optional(optional($c->booking)->service)->name }}</td>
                        <td>PKR {{ number_format($c->total_amount) }}</td>
                        <td class="text-success fw-semibold">PKR {{ number_format($c->commission_amount) }}</td>
                        <td>PKR {{ number_format($c->provider_earning) }}</td>
                        <td class="text-muted small">{{ $c->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $commissions->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="far fa-calendar-alt fa-3x" style="color: #cbd5e1;"></i>
                </div>
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
