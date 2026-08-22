@extends('layouts.app')
@section('content')
    <div class="container-fluid px-4 py-3">
        <h3 class="fw-bold mb-4"><i class="fas fa-crown text-warning me-2"></i>Admin Dashboard</h3>

        {{-- Stats --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="glass-card p-3 rounded-4 text-center">
                    <i class="fas fa-store fa-2x text-primary mb-2"></i>
                    <div class="fs-3 fw-bold">{{ $providers->count() }}</div>
                    <div class="text-muted small">Providers</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="glass-card p-3 rounded-4 text-center">
                    <i class="fas fa-calendar-check fa-2x text-success mb-2"></i>
                    <div class="fs-3 fw-bold">{{ $totalBookings }}</div>
                    <div class="text-muted small">Total Bookings</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="glass-card p-3 rounded-4 text-center">
                    <i class="fas fa-money-bill-wave fa-2x text-info mb-2"></i>
                    <div class="fs-3 fw-bold">PKR {{ number_format($totalRevenue) }}</div>
                    <div class="text-muted small">Commission Earned</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="glass-card p-3 rounded-4 text-center">
                    <i class="fas fa-handshake fa-2x text-warning mb-2"></i>
                    <div class="fs-3 fw-bold">PKR {{ number_format($totalEarning) }}</div>
                    <div class="text-muted small">Provider Earnings</div>
                </div>
            </div>
        </div>

        {{-- Quick Links --}}
        @if(Route::has('admin.services.index'))
            <div class="d-flex gap-2 mb-4 flex-wrap">
                <a href="{{ route('admin.services.index') }}" class="btn btn-outline-primary rounded-pill">
                    <i class="fas fa-concierge-bell me-1"></i> Services
                </a>
            </div>
        @endif

        {{-- Bookings Filter + Table --}}
        <div class="glass-card p-4 rounded-4 shadow">
            <h5 class="fw-bold mb-3">
                <i class="fas fa-calendar-alt me-2 text-primary"></i>
                {{ $filtered ? 'Filtered Bookings' : 'Recent Bookings (Last 5)' }}
            </h5>

            {{-- Filter Form --}}
            <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-2 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold small text-muted text-uppercase">Date</label>
                    <input type="date" name="filter_date" class="form-control rounded-3" value="{{ $filterDate }}"
                        placeholder="Select date">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold small text-muted text-uppercase">Service Provider</label>
                    <select name="filter_provider" class="form-select rounded-3">
                        <option value="">All Providers</option>
                        @foreach($providers as $p)
                            <option value="{{ $p->id }}" {{ $filterProvider == $p->id ? 'selected' : '' }}>
                                {{ $p->business_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill flex-grow-1">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                    @if($filtered)
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary rounded-pill">Clear</a>
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
                                <th>Provider</th>
                                <th>Service</th>
                                <th>Car</th>
                                <th>Appointment</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="bookings-tbody">
                            @include("admin.partials.bookings_table_body")
                        </tbody>
                    </table>
                </div>

                {{-- Pagination / Show All --}}
                @if($filtered)
                    @if(!$showAll && $bookingTotal > 10)
                        <div class="text-center mt-3">
                            <p class="text-muted small mb-2">Showing 10 of {{ $bookingTotal }} bookings</p>
                            <a href="{{ route('admin.dashboard', array_merge(request()->query(), ['show_all' => 1])) }}"
                                class="btn btn-outline-primary rounded-pill px-4">
                                <i class="fas fa-chevron-down me-1"></i> Show All {{ $bookingTotal }} Bookings
                            </a>
                        </div>
                    @elseif($showAll)
                        <div class="text-center mt-3">
                            <p class="text-muted small mb-2">Showing all {{ $bookingTotal }} bookings</p>
                            <a href="{{ route('admin.dashboard', array_diff_key(request()->query(), ['show_all' => ''])) }}"
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
            setInterval(function () {
                fetch(window.location.href, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                    .then(r => r.json())
                    .then(d => { if (d.html) document.getElementById('bookings-tbody').innerHTML = d.html; })
                    .catch(e => console.error('Polling error', e));
            }, 10000); // 10 seconds
        </script>
    @endpush
@endsection