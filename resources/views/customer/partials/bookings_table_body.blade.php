@if($bookings->isEmpty())
    <tr>
        <td colspan="6" class="text-center py-5">
            <i class="fas fa-calendar-times fa-3x text-muted mb-3 opacity-50"></i>
            <h5 class="text-muted">No bookings yet</h5>
        </td>
    </tr>
@else
@foreach($bookings as $booking)
@php
    $statusColor = match($booking->status) {
        'confirmed'       => 'success',
        'accepted'        => 'info',
        'assigned'        => 'info',
        'in_progress'     => 'primary',
        'completed'       => 'dark',
        'cancelled'       => 'danger',
        default           => 'secondary',
    };
    $payment = $booking->payment;
@endphp

<tr>
    <td class="ps-3 fw-semibold text-muted small">
        #{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}
    </td>
    <td class="fw-semibold">{{ optional($booking->service)->name ?? '—' }}</td>
    <td class="text-muted small">
        <i class="fas fa-store me-1 text-primary"></i>
        {{ optional($booking->serviceProvider)->business_name ?? '—' }}
    </td>
    <td class="small">
        @if($booking->appointment_time)
            <div class="fw-semibold">{{ $booking->appointment_time->format('d M Y') }}</div>
            <small class="text-muted">{{ $booking->appointment_time->format('h:i A') }}</small>
        @else
            <span class="text-muted fst-italic">TBD</span>
        @endif
    </td>
    <td>
        <span class="badge bg-{{ $statusColor }} rounded-pill px-3 py-1 text-capitalize">
            {{ str_replace('_', ' ', $booking->status) }}
        </span>
    </td>
    <td class="text-end pe-3">
        @if($booking->status === 'completed')
            @if($booking->rating)
                <span class="badge bg-warning text-dark rounded-pill px-2 py-1 small" title="Rated {{ $booking->rating->rating }} Stars">
                    <i class="fas fa-star text-warning me-1"></i>{{ $booking->rating->rating }}★ Rated
                </span>
            @else
                <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-semibold shadow-sm"
                        data-bs-toggle="modal" data-bs-target="#rateModal{{ $booking->id }}">
                    <i class="fas fa-star me-1"></i>Rate Service
                </button>
            @endif
        @elseif(!in_array($booking->status, ['cancelled', 'in_progress', 'completed']) && $booking->created_at->diffInMinutes(now()) <= 15)
            {{-- Cancel button: only visible within 15 minutes of booking creation --}}
            <form action="{{ route('bookings.cancel', $booking) }}" method="POST"
                  onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold shadow-sm">
                    <i class="fas fa-times-circle me-1"></i>Cancel
                </button>
            </form>
        @else
            <span class="text-muted small">—</span>
        @endif
    </td>
</tr>
@endforeach
@endif
