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
                <tbody>
                @foreach($bookings as $b)
                @php
                    $sc = match($b->status) {
                        'confirmed'       => 'success',
                        'payment_pending' => 'warning',
                        'accepted'        => 'info',
                        'assigned'        => 'info',
                        'in_progress'     => 'primary',
                        'completed'       => 'dark',
                        'cancelled'       => 'danger',
                        default           => 'secondary',
                    };
                    $canAssign = !in_array($b->status, ['completed', 'cancelled']) && $workers->count();
                @endphp
                <tr>
                    <td class="fw-semibold">#{{ str_pad($b->id, 5, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ optional($b->user)->name }}</td>
                    <td>{{ optional($b->service)->name }}</td>
                    <td class="small text-muted">{{ optional($b->carModel)->name ?? '—' }}</td>
                    <td class="small">{{ $b->appointment_time?->format('d M Y h:i A') }}</td>
                    <td class="fw-bold">PKR {{ number_format($b->final_price) }}</td>
                    <td><span class="badge bg-{{ $sc }} rounded-pill text-capitalize">{{ str_replace('_', ' ', $b->status) }}</span></td>

                    {{-- Worker column: shows assigned worker name + Assign/Change button --}}
                    <td>
                        @if($b->worker)
                            <div class="small fw-semibold mb-1">
                                <i class="fas fa-hard-hat text-warning me-1"></i>{{ $b->worker->name }}
                            </div>
                        @endif

                        @if($canAssign)
                            {{-- Toggle button --}}
                            <button class="btn btn-sm btn-outline-primary rounded-pill"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#assignForm{{ $b->id }}"
                                    aria-expanded="false">
                                <i class="fas fa-user-plus me-1"></i>{{ $b->worker ? 'Change' : 'Assign' }}
                            </button>

                            {{-- Collapsible dropdown form --}}
                            <div class="collapse mt-2" id="assignForm{{ $b->id }}">
                                <form action="{{ route('provider.bookings.assign', $b) }}" method="POST">
                                    @csrf
                                    <div class="input-group input-group-sm">
                                        <select name="worker_id" class="form-select form-select-sm" required>
                                            <option value="">Select worker…</option>
                                            @foreach($workers as $w)
                                                <option value="{{ $w->id }}"
                                                    {{ $b->provider_worker_id == $w->id ? 'selected' : '' }}>
                                                    {{ $w->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @elseif(!$b->worker)
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
