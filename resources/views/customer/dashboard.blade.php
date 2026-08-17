@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">

    {{-- Header row with title + Book New Service button --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">
            <i class="fas fa-calendar-check text-primary me-2"></i>My Bookings
        </h3>

        @if($lastProvider)
            <a href="{{ route('providers.show', $lastProvider) }}"
               class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="fas fa-plus-circle me-2"></i>Book New Service
            </a>
        @else
            <a href="{{ route('welcome') }}"
               class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="fas fa-search-location me-2"></i>Find a Provider
            </a>
        @endif
    </div>

    @if($bookings->isEmpty())
        <div class="text-center py-5">
            <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
            <h5 class="text-muted">No bookings yet</h5>
            <a href="{{ route('welcome') }}" class="btn btn-primary rounded-pill mt-2">Find Providers</a>
        </div>
    @else
        <div class="glass-card p-4 rounded-4 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Service</th>
                            <th>Provider</th>
                            <th>Car</th>
                            <th>Appointment</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
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
                        {{-- Booking # --}}
                        <td class="ps-3 fw-semibold text-muted small">
                            #{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}
                        </td>

                        {{-- Service --}}
                        <td class="fw-semibold">{{ optional($booking->service)->name ?? '—' }}</td>

                        {{-- Provider --}}
                        <td class="text-muted small">
                            <i class="fas fa-store me-1 text-primary"></i>
                            {{ optional($booking->serviceProvider)->business_name ?? '—' }}
                        </td>

                        {{-- Car --}}
                        <td class="text-muted small">{{ optional($booking->carModel)->name ?? '—' }}</td>

                        {{-- Appointment --}}
                        <td class="small">
                            @if($booking->appointment_time)
                                <div class="fw-semibold">{{ $booking->appointment_time->format('d M Y') }}</div>
                                <small class="text-muted">{{ $booking->appointment_time->format('h:i A') }}</small>
                            @else
                                <span class="text-muted fst-italic">TBD</span>
                            @endif
                        </td>

                        {{-- Amount --}}
                        <td class="fw-bold text-primary">PKR {{ number_format($booking->final_price) }}</td>

                        {{-- Payment status --}}
                        <td>
                            <span class="badge rounded-pill {{ $payment && $payment->status === 'paid' ? 'bg-success' : 'bg-secondary' }}">
                                {{ $payment ? ucfirst($payment->status) : 'No Payment' }}
                            </span>
                        </td>

                        {{-- Booking status --}}
                        <td>
                            <span class="badge bg-{{ $statusColor }} rounded-pill text-capitalize">
                                {{ str_replace('_', ' ', $booking->status) }}
                            </span>
                        </td>

                        {{-- Actions --}}
                        <td class="text-end pe-3">
                            <div class="d-flex gap-2 justify-content-end">
                                {{-- Cancel --}}
                                @if(!in_array($booking->status, ['in_progress','completed','cancelled']) && $booking->created_at->diffInMinutes(now()) <= 15)
                                <form action="{{ route('bookings.cancel', $booking) }}" method="POST"
                                      onsubmit="return confirm('Cancel this booking?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">
                                        <i class="fas fa-times me-1"></i>Cancel
                                    </button>
                                </form>
                                @endif

                                {{-- Rate --}}
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
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

{{-- Rating Modals (outside the table) --}}
@foreach($bookings as $booking)
    @if($booking->status === 'completed' && !$booking->rating)
    <div class="modal fade" id="rateModal{{ $booking->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-star text-warning me-2"></i>Rate Service</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4 pb-4">
                    <form action="{{ route('bookings.rate', $booking) }}" method="POST">
                        @csrf
                        <p class="text-muted small mb-3">
                            {{ optional($booking->service)->name }} at
                            {{ optional($booking->serviceProvider)->business_name }}
                        </p>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Rating</label>
                            <div class="d-flex gap-3">
                                @for($i=1; $i<=5; $i++)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio"
                                           name="rating"
                                           id="r{{ $booking->id }}_{{ $i }}"
                                           value="{{ $i }}"
                                           {{ $i==5 ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold"
                                           for="r{{ $booking->id }}_{{ $i }}">{{ $i }}★</label>
                                </div>
                                @endfor
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Review <span class="text-muted">(optional)</span>
                            </label>
                            <textarea name="review" class="form-control rounded-3" rows="3"
                                      placeholder="Share your experience..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-warning w-100 rounded-pill fw-bold">
                            <i class="fas fa-check me-2"></i>Submit Rating
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach

@endsection
