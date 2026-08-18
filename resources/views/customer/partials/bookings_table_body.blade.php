@foreach($bookings as $booking)
@php
    $statusColor = match($booking->status) {
        'confirmed'       => 'success',
        'payment_pending' => 'warning',
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
    <td class="text-muted small">{{ optional($booking->carModel)->name ?? '—' }}</td>
    <td class="small">
        @if($booking->appointment_time)
            <div class="fw-semibold">{{ $booking->appointment_time->format('d M Y') }}</div>
            <small class="text-muted">{{ $booking->appointment_time->format('h:i A') }}</small>
        @else
            <span class="text-muted fst-italic">TBD</span>
        @endif
    </td>
    <td class="fw-bold text-primary">PKR {{ number_format($booking->final_price) }}</td>
    <td>
        <span class="badge rounded-pill {{ $payment && $payment->status === 'paid' ? 'bg-success' : 'bg-secondary' }}">
            {{ $payment ? ucfirst($payment->status) : 'No Payment' }}
        </span>
    </td>
    <td>
        <span class="badge bg-{{ $statusColor }} rounded-pill text-capitalize">
            {{ str_replace('_', ' ', $booking->status) }}
        </span>
    </td>
    <td class="text-end pe-3">
        <div class="d-flex gap-2 justify-content-end">
            @if(!in_array($booking->status, ['in_progress','completed','cancelled']) && $booking->created_at->diffInMinutes(now()) <= 15)
            <form action="{{ route('bookings.cancel', $booking) }}" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
            </form>
            @endif

            @if($booking->status === 'completed' && !$booking->rating)
            <button class="btn btn-sm btn-outline-warning rounded-pill"
                    data-bs-toggle="modal"
                    data-bs-target="#rateModal{{ $booking->id }}">
                <i class="fas fa-star me-1"></i>Rate
            </button>
            @elseif($booking->rating)
            <span class="text-muted small align-self-center">
                ⭐ {{ $booking->rating->rating }}/5
            </span>
            @endif
        </div>
    </td>
</tr>
@endforeach
