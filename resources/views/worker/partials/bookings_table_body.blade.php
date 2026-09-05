@if($assignedBookings->isEmpty())
    <tr>
        <td colspan="7" class="text-center py-5">
            <i class="fas fa-calendar-times fa-3x text-muted mb-3 opacity-50"></i>
            <h5 class="text-muted">No bookings assigned to you yet.</h5>
        </td>
    </tr>
@else
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
<tr class="{{ !$isActionable && !in_array($b->status, ['completed','cancelled']) ? 'opacity-75' : '' }}">
    <td class="ps-3 fw-semibold text-muted small">
        #{{ str_pad($b->id, 5, '0', STR_PAD_LEFT) }}
    </td>
    <td class="fw-semibold">{{ optional($b->service)->name ?? '—' }}</td>
    <td class="text-muted small">
        <i class="fas fa-user me-1 text-primary"></i>
        {{ optional($b->user)->name ?? '—' }}
    </td>
    <td class="text-muted small">
        <i class="fas fa-store me-1 text-primary"></i>
        {{ optional($b->serviceProvider)->business_name ?? '—' }}
    </td>
    <td class="small">
        @if($b->appointment_time)
            <div class="fw-semibold">{{ $b->appointment_time->format('d M Y') }}</div>
            <small class="text-muted">{{ $b->appointment_time->format('h:i A') }}</small>
        @else
            <span class="text-muted fst-italic">TBD</span>
        @endif
    </td>
    <td>
        <span class="badge bg-{{ $sc }} rounded-pill text-capitalize">
            {{ str_replace('_', ' ', $b->status) }}
        </span>
    </td>
    <td class="text-end pe-3">
        <div class="d-flex gap-2 justify-content-end align-items-center text-nowrap">
            @if($isActionable && $b->status === 'assigned')
                <form action="{{ route('bookings.status', $b) }}" method="POST" class="d-inline mb-0">
                    @csrf @method('PATCH')
                    <button type="submit" name="status" value="in_progress"
                            class="btn btn-sm btn-primary rounded-pill text-nowrap px-3">
                        <i class="fas fa-play me-1"></i>Start Job
                    </button>
                </form>
            @elseif($isActionable && $b->status === 'in_progress')
                <form action="{{ route('bookings.status', $b) }}" method="POST" class="d-inline mb-0">
                    @csrf @method('PATCH')
                    <button type="submit" name="status" value="completed"
                            class="btn btn-sm btn-success rounded-pill text-nowrap px-3">
                        <i class="fas fa-check me-1"></i>Mark Complete
                    </button>
                </form>
            @elseif(!in_array($b->status, ['completed', 'cancelled']) && !$isActionable)
                <div class="text-muted small border rounded-3 px-2 py-1 text-nowrap d-inline-block">
                    <i class="fas fa-lock me-1"></i>Locked
                </div>
            @endif
        </div>
    </td>
</tr>
@endforeach
@endif
