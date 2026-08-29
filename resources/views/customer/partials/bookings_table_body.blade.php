@if($bookings->isEmpty())
    <tr>
        <td colspan="4" class="text-center py-5">
            <i class="fas fa-calendar-times fa-3x text-muted mb-3 opacity-50"></i>
            <h5 class="text-muted">No bookings yet</h5>
        </td>
    </tr>
@else
@foreach($bookings as $booking)
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
</tr>
@endforeach
@endif
