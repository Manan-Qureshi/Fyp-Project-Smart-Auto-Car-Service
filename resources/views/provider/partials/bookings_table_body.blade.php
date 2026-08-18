@if($bookings->isEmpty())
    <tr>
        <td colspan="8" class="text-center py-4 text-muted">No bookings yet.</td>
    </tr>
@else
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
    $canAssign = !in_array($b->status, ['completed', 'cancelled']) && count($workers ?? []);
@endphp
<tr>
    <td class="fw-semibold">#{{ str_pad($b->id, 5, '0', STR_PAD_LEFT) }}</td>
    <td>{{ optional($b->user)->name }}</td>
    <td>{{ optional($b->service)->name }}</td>
    <td class="small text-muted">{{ optional($b->carModel)->name ?? '—' }}</td>
    <td class="small">{{ $b->appointment_time?->format('d M Y h:i A') }}</td>
    <td class="fw-bold">PKR {{ number_format($b->final_price) }}</td>
    <td>
        <span class="badge bg-{{ $sc }} rounded-pill text-capitalize">{{ str_replace('_', ' ', $b->status) }}</span>
    </td>
    <td>
        @if($b->worker)
            <div class="small fw-semibold mb-1">
                <i class="fas fa-hard-hat text-warning me-1"></i>{{ $b->worker->name }}
            </div>
        @endif

        @if($canAssign)
            <button class="btn btn-sm btn-outline-primary rounded-pill"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#assignForm{{ $b->id }}"
                    aria-expanded="false">
                <i class="fas fa-user-plus me-1"></i>{{ $b->worker ? 'Change' : 'Assign' }}
            </button>

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
@endif
